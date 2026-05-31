<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\StudentPayment;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PaymentService
{
    /**
     * Build a safe snapshot array for storage in student_payments.detail_fee_snapshot.
     *
     * Expected format: [{ label: string, amount: number, qty?: number }]
     */
    public function buildDetailFeeSnapshot(Payment $payment): array
    {
        $template = $payment->detail_fee_template;

        if (!is_array($template) || count($template) === 0) {
            // Fallback to a single component if template isn't configured.
            return [
                [
                    'label' => 'Total',
                    'amount' => (float)($payment->default_amount ?? 0),
                    'qty' => 1,
                ],
            ];
        }

        $snapshot = [];
        foreach ($template as $row) {
            if (!is_array($row)) continue;

            $label = trim((string)Arr::get($row, 'label', ''));
            if ($label === '') {
                $label = 'Komponen';
            }

            $amount = (float)Arr::get($row, 'amount', 0);
            $qty = (int)Arr::get($row, 'qty', 1);
            if ($qty <= 0) $qty = 1;

            $snapshot[] = [
                'label' => $label,
                'amount' => $amount,
                'qty' => $qty,
            ];
        }

        return $snapshot;
    }

    public function computeTotalFromSnapshot(array $snapshot, float $fallback = 0): float
    {
        $sum = 0.0;

        foreach ($snapshot as $row) {
            if (!is_array($row)) continue;
            $amount = (float)Arr::get($row, 'amount', 0);
            $qty = (int)Arr::get($row, 'qty', 1);
            if ($qty <= 0) $qty = 1;
            $sum += ($amount * $qty);
        }

        if ($sum <= 0 && $fallback > 0) {
            return $fallback;
        }

        return $sum;
    }

    /**
     * Create a student payment (header + snapshot totals).
     *
     * Enforces uniqueness at the application level; DB has a unique index too.
     */
    public function createStudentPayment(array $payload): StudentPayment
    {
        /** @var Payment $payment */
        $payment = $payload['payment'];
        $studentId = (int)$payload['id_student'];
        $paymentPeriod = (string)$payload['payment_period'];
        $discountAmount = (float)($payload['discount_amount'] ?? 0);

        $snapshot = $this->buildDetailFeeSnapshot($payment);
        $total = $this->computeTotalFromSnapshot($snapshot, (float)($payment->default_amount ?? 0));

        $final = $total - $discountAmount;
        if ($final < 0) $final = 0;

        return StudentPayment::create([
            'id_student' => $studentId,
            'id_payment' => (int)$payment->id_payment,
            'payment_period' => $paymentPeriod,
            'detail_fee_snapshot' => $snapshot,
            'total_amount' => $total,
            'discount_amount' => $discountAmount,
            'final_amount' => $final,
            'status' => 'pending',
            'installment_requested' => false,
        ]);
    }

    /**
     * Build an installment schedule for a given total.
     *
     * Splits using integer cents to avoid rounding drift. Any remainder is added
     * to the last installment.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildInstallmentSchedule(float $totalAmount, int $count, Carbon $firstDueDate, int $intervalMonths = 1): array
    {
        if ($count < 2) {
            throw new \InvalidArgumentException('Installment count must be >= 2');
        }

        if ($intervalMonths < 1) {
            $intervalMonths = 1;
        }

        $totalCents = (int) round(max(0, $totalAmount) * 100);
        $baseCents = intdiv($totalCents, $count);
        $remainder = $totalCents - ($baseCents * $count);

        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $amountCents = $baseCents;
            if ($i === $count) {
                $amountCents += $remainder;
            }

            $dueDate = (clone $firstDueDate)->addMonths(($i - 1) * $intervalMonths);

            $rows[] = [
                'installment_number' => $i,
                'due_date' => $dueDate->toDateString(),
                'installment_amount' => $amountCents / 100,
                'status' => 'pending',
            ];
        }

        return $rows;
    }

    /**
     * Sync student_payments.status + paid_at based on installment statuses.
     *
     * If all installments are paid, mark header paid; otherwise keep pending.
     */
    public function syncStudentPaymentStatusFromInstallments(StudentPayment $studentPayment): void
    {
        $installments = $studentPayment->installments()
            ->orderBy('installment_number')
            ->get(['status', 'paid_at', 'payment_method']);

        if ($installments->isEmpty()) {
            return;
        }

        $allPaid = $installments->every(fn($i) => ($i->status ?? 'pending') === 'paid');

        if ($allPaid) {
            $latest = $installments->filter(fn($i) => $i->paid_at)->sortByDesc('paid_at')->first();
            $studentPayment->update([
                'status' => 'paid',
                'paid_at' => $latest?->paid_at ?? now(),
                'payment_method' => $latest?->payment_method ?? $studentPayment->payment_method,
            ]);
            return;
        }

        $studentPayment->update([
            'status' => 'pending',
            'paid_at' => null,
        ]);
    }
}
