<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentPayment;
use Illuminate\Database\Seeder;

class StudentPaymentSeeder extends Seeder
{
    /**
     * Seed tagihan murid:
     * - uang_pendaftaran  : semua murid, status lunas
     * - iuran_masuk       : semua murid, status lunas
     * - iuran_bulanan     : Jan–Mei 2026, variasi status (lunas/menunggu_konfirmasi/belum_bayar)
     */
    public function run(): void
    {
        $students = Student::all();
        if ($students->isEmpty()) {
            return;
        }

        $pendaftaran = Payment::query()->where('jenis_payment', 'uang_pendaftaran')->first();
        $masuk       = Payment::query()->where('jenis_payment', 'iuran_masuk')->first();
        $bulanan     = Payment::query()->where('jenis_payment', 'iuran_bulanan')->first();

        if (! $pendaftaran || ! $masuk || ! $bulanan) {
            return;
        }

        foreach ($students as $student) {
            // ── Uang Pendaftaran ─────────────────────────────
            $this->ensurePayment($student->id_student, $pendaftaran->id_payment, 'ONCE', [
                'total_amount'   => 200_000,
                'discount_amount' => 0,
                'final_amount'   => 200_000,
                'status'         => 'paid',
                'paid_at'        => now()->subMonths(8),
                'payment_method' => 'transfer_bank',
                'detail_fee_snapshot' => [
                    ['label' => 'Uang Pendaftaran', 'amount' => 200_000, 'qty' => 1],
                ],
            ]);

            // ── Iuran Masuk ──────────────────────────────────
            $this->ensurePayment($student->id_student, $masuk->id_payment, 'ONCE', [
                'total_amount'   => 1_500_000,
                'discount_amount' => 0,
                'final_amount'   => 1_500_000,
                'status'         => 'paid',
                'paid_at'        => now()->subMonths(7),
                'payment_method' => 'transfer_bank',
                'detail_fee_snapshot' => [
                    ['label' => 'Uang Pembangunan', 'amount' => 500_000, 'qty' => 1],
                    ['label' => 'Seragam',          'amount' => 300_000, 'qty' => 1],
                    ['label' => 'ATK',              'amount' => 150_000, 'qty' => 1],
                    ['label' => 'Sarana',           'amount' => 200_000, 'qty' => 1],
                    ['label' => 'Buku Paket',       'amount' => 250_000, 'qty' => 1],
                    ['label' => 'Sampul Rapor',     'amount' => 100_000, 'qty' => 1],
                ],
            ]);

            // ── Iuran Bulanan Jan–Mei 2026 ───────────────────
            // status: paid=sudah bayar, pending=sudah upload bukti/belum dikonfirmasi, failed=belum bayar
            $months = [
                ['period' => '2026-01', 'status' => 'paid',    'months_ago' => 4],
                ['period' => '2026-02', 'status' => 'paid',    'months_ago' => 3],
                ['period' => '2026-03', 'status' => 'paid',    'months_ago' => 2],
                ['period' => '2026-04', 'status' => 'pending', 'months_ago' => 1],
                ['period' => '2026-05', 'status' => 'pending', 'months_ago' => null],
            ];

            foreach ($months as $m) {
                $this->ensurePayment($student->id_student, $bulanan->id_payment, $m['period'], [
                    'total_amount'   => 300_000,
                    'discount_amount' => 0,
                    'final_amount'   => 300_000,
                    'status'         => $m['status'],
                    'paid_at'        => $m['months_ago'] !== null ? now()->subMonths($m['months_ago']) : null,
                    'payment_method' => 'transfer_bank',
                    'detail_fee_snapshot' => [
                        ['label' => 'Iuran Bulanan', 'amount' => 300_000, 'qty' => 1],
                    ],
                ]);
            }
        }
    }

    private function ensurePayment(int $idStudent, int $idPayment, string $period, array $data): void
    {
        $query = StudentPayment::query()
            ->where('id_student', $idStudent)
            ->where('id_payment', $idPayment)
            ->where('payment_period', $period);

        if ($query->exists()) {
            return;
        }

        StudentPayment::query()->create(array_merge($data, [
            'id_student'     => $idStudent,
            'id_payment'     => $idPayment,
            'payment_period' => $period,
            'installment_requested' => false,
            'installment_count'     => null,
        ]));
    }
}
