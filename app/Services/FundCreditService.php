<?php

namespace App\Services;

use App\Models\FundSource;
use App\Models\FundTransaction;
use App\Models\StudentPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FundCreditService
{
    /**
     * Catat fund_transactions(in) untuk setiap komponen pada StudentPayment yang baru lunas.
     *
     * Idempotent: jika sudah ada transactions dengan reference_type='student_payment'
     * dan reference_id=$payment->id, fungsi return tanpa nulis ulang.
     *
     * Mapping komponen → source code dari config/fund_sources.php.
     * Komponen yang tidak match mapping → masuk fallback_source.
     */
    public function creditFromStudentPayment(StudentPayment $payment, ?User $createdBy = null): void
    {
        $alreadyCredited = FundTransaction::query()
            ->where('reference_type', 'student_payment')
            ->where('reference_id', $payment->id_student_payment)
            ->exists();
        if ($alreadyCredited) {
            return;
        }

        $sourcesByCode = FundSource::query()->active()->get()->keyBy('code');
        $componentMapping = config('fund_sources.component_mapping', []);
        $jenisPaymentMapping = config('fund_sources.jenis_payment_mapping', []);
        $fallbackCode = (string) config('fund_sources.fallback_source', 'lain_lain');

        $payment->loadMissing('payment');
        $jenisPayment = (string) ($payment->payment?->jenis_payment ?? '');

        $transactionDate = ($payment->paid_at ?? now())->toDateString();
        $createdById = $createdBy?->id ?? auth()->id();

        DB::transaction(function () use ($payment, $sourcesByCode, $componentMapping, $jenisPaymentMapping, $fallbackCode, $transactionDate, $createdById, $jenisPayment) {
            $snapshot = $payment->detail_fee_snapshot;

            // Jika tidak ada snapshot komponen, treat sebagai monolitik berdasarkan jenis_payment.
            if (!is_array($snapshot) || count($snapshot) === 0) {
                $code = $jenisPaymentMapping[$jenisPayment] ?? $fallbackCode;
                $this->insert(
                    sourcesByCode: $sourcesByCode,
                    code: $code,
                    fallbackCode: $fallbackCode,
                    amount: (float) ($payment->final_amount ?? $payment->total_amount ?? 0),
                    transactionDate: $transactionDate,
                    description: "Pembayaran {$jenisPayment} #{$payment->id_student_payment}",
                    referenceId: (int) $payment->id_student_payment,
                    createdById: $createdById
                );
                return;
            }

            foreach ($snapshot as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $label = trim((string) ($row['label'] ?? ''));
                $amount = (float) ($row['amount'] ?? 0);
                $qty = (int) ($row['qty'] ?? 1);
                $total = $amount * max(1, $qty);

                if ($total <= 0 || $label === '') {
                    continue;
                }

                $normalized = mb_strtolower($label);
                $code = $componentMapping[$normalized]
                    ?? $jenisPaymentMapping[$jenisPayment]
                    ?? $fallbackCode;

                $this->insert(
                    sourcesByCode: $sourcesByCode,
                    code: $code,
                    fallbackCode: $fallbackCode,
                    amount: $total,
                    transactionDate: $transactionDate,
                    description: "Komponen '{$label}' dari {$jenisPayment} #{$payment->id_student_payment}",
                    referenceId: (int) $payment->id_student_payment,
                    createdById: $createdById
                );
            }
        });
    }

    private function insert(
        \Illuminate\Support\Collection $sourcesByCode,
        string $code,
        string $fallbackCode,
        float $amount,
        string $transactionDate,
        string $description,
        int $referenceId,
        ?int $createdById,
    ): void {
        $source = $sourcesByCode->get($code) ?? $sourcesByCode->get($fallbackCode);
        if (!$source) {
            return;
        }

        FundTransaction::create([
            'id_fund_source' => (int) $source->id,
            'direction' => 'in',
            'amount' => $amount,
            'transaction_date' => $transactionDate,
            'description' => $description,
            'attachment_path' => null,
            'reference_type' => 'student_payment',
            'reference_id' => $referenceId,
            'created_by' => $createdById,
        ]);
    }
}
