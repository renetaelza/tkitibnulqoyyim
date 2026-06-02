<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentPaymentCreated;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\StudentPaymentInstallment;
use App\Services\FundCreditService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentPaymentManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentPayment::query()->with(['student', 'payment']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_period', 'like', "%{$search}%")
                    ->orWhereHas('student', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('payment', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        // Tab filter (default: unpaid). Nilai: unpaid | paid | awaiting_approval | all
        $tab = $request->input('tab', 'unpaid');
        if (!in_array($tab, ['unpaid', 'paid', 'awaiting_approval', 'all'], true)) {
            $tab = 'unpaid';
        }
        if ($tab === 'unpaid') {
            $query->whereIn('status', ['pending', 'failed']);
        } elseif ($tab === 'paid') {
            $query->where('status', 'paid');
        } elseif ($tab === 'awaiting_approval') {
            $query->whereHas('proofs', fn($q) => $q->where('status', 'pending'));
        }

        if ($request->filled('id_payment') && $request->input('id_payment') !== 'all') {
            $query->where('id_payment', (int)$request->input('id_payment'));
        }

        $query->orderByDesc('created_at');

        $perPage = (int)$request->input('per_page', 10);
        $studentPayments = $query->paginate($perPage)->appends($request->query());

        $payments = Payment::query()->orderBy('name')->get(['id_payment', 'name']);

        // Counts per tab untuk badge.
        $tabCounts = [
            'unpaid' => (int) StudentPayment::query()->whereIn('status', ['pending', 'failed'])->count(),
            'paid' => (int) StudentPayment::query()->where('status', 'paid')->count(),
            'awaiting_approval' => (int) StudentPayment::query()
                ->whereHas('proofs', fn($q) => $q->where('status', 'pending'))
                ->count(),
        ];

        return view('dashboard.admin.student-payments', [
            'studentPayments' => $studentPayments,
            'payments' => $payments,
            'search' => $request->input('search', ''),
            'tab' => $tab,
            'id_payment' => $request->input('id_payment', 'all'),
            'per_page' => $perPage,
            'tabCounts' => $tabCounts,
        ]);
    }

    public function create()
    {
        $students = Student::query()->orderBy('name')->get(['id_student', 'name']);
        $payments = Payment::query()->where('is_active', true)->orderBy('name')->get([
            'id_payment', 'name', 'jenis_payment', 'period_mode', 'default_amount', 'detail_fee_template',
        ]);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'student-payment',
            'action' => 'create',
            'studentPayment' => null,
            'students' => $students,
            'payments' => $payments,
        ])->render()]);
    }

    public function store(Request $request, PaymentService $paymentService)
    {
        $validated = $request->validate([
            'id_student' => ['required', 'integer', 'exists:students,id_student'],
            'id_payment' => ['required', 'integer', 'exists:payments,id_payment'],
            'payment_period' => ['nullable', 'string', 'max:20'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $payment = Payment::findOrFail((int)$validated['id_payment']);
        $paymentPeriod = $this->normalizePaymentPeriod($payment->period_mode, $validated['payment_period'] ?? null);

        $exists = StudentPayment::query()
            ->where('id_student', (int)$validated['id_student'])
            ->where('id_payment', (int)$payment->id_payment)
            ->where('payment_period', $paymentPeriod)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'payment_period' => 'Tagihan untuk murid + payment + periode ini sudah ada. Silakan edit tagihan yang sudah dibuat.',
            ]);
        }

        $studentPayment = $paymentService->createStudentPayment([
            'payment' => $payment,
            'id_student' => (int)$validated['id_student'],
            'payment_period' => $paymentPeriod,
            'discount_amount' => (float)($validated['discount_amount'] ?? 0),
        ]);

        $this->sendStudentPaymentCreatedEmail($studentPayment);

        return response()->json(['success' => true, 'message' => 'Tagihan murid berhasil dibuat', 'data' => ['id' => $studentPayment->id_student_payment]]);
    }

    public function edit(StudentPayment $studentPayment)
    {
        $studentPayment->load(['student', 'payment']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'student-payment',
            'action' => 'edit',
            'studentPayment' => $studentPayment,
        ])->render()]);
    }

    public function show(StudentPayment $studentPayment)
    {
        $studentPayment->load([
            'student',
            'payment',
            'proofs' => fn($q) => $q->latest('id_payment_proof')->with('uploadedBy'),
            'installments' => fn($q) => $q->orderBy('installment_number'),
            'installments.proofs' => fn($q) => $q->latest('id_payment_proof')->with('uploadedBy'),
        ]);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'student-payment',
            'studentPayment' => $studentPayment,
        ])->render()]);
    }

    public function proofApprove(Request $request, PaymentProof $paymentProof, PaymentService $paymentService)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:255'],
        ]);

        if (($paymentProof->status ?? 'pending') !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Bukti ini sudah diproses sebelumnya.',
            ], 422);
        }

        $paymentProof->load('proofable');
        $proofable = $paymentProof->proofable;
        if (!$proofable) {
            return response()->json([
                'success' => false,
                'message' => 'Data tagihan/cicilan untuk bukti ini tidak ditemukan.',
            ], 404);
        }

        return DB::transaction(function () use ($paymentProof, $proofable, $validated, $paymentService) {
            if (($paymentProof->status ?? 'pending') !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti ini sudah diproses sebelumnya.',
                ], 422);
            }

            if ($proofable instanceof StudentPayment) {
                // If this payment uses installments, prevent approving header proof.
                $hasInstallments = $proofable->installments()->exists();
                if ($hasInstallments) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tagihan ini menggunakan cicilan. Verifikasi bukti harus dilakukan pada cicilan.',
                    ], 422);
                }

                if (($proofable->status ?? 'pending') === 'paid') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tagihan sudah Paid.',
                    ], 422);
                }

                $paymentProof->update([
                    'status' => 'approved',
                    'admin_note' => $validated['admin_note'] ?? null,
                ]);

                PaymentProof::query()
                    ->where('proofable_type', $paymentProof->proofable_type)
                    ->where('proofable_id', (int)$paymentProof->proofable_id)
                    ->where('id_payment_proof', '<>', (int)$paymentProof->id_payment_proof)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'rejected',
                        'admin_note' => 'Otomatis ditolak: ada bukti lain yang disetujui.',
                    ]);

                $proofable->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $paymentProof->payment_method ?? $proofable->payment_method,
                    // keep latest for backward compatibility
                    'proof_file' => $paymentProof->file_path ?? $proofable->proof_file,
                ]);

                // Auto-credit ke fund_sources (Bendahara) saat StudentPayment fully paid.
                app(FundCreditService::class)->creditFromStudentPayment($proofable->fresh(['payment']));

                $activationNote = $this->maybeActivateRegistrationAfterRegistrationFeePaid($proofable);

                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pembayaran disetujui. Tagihan ditandai Paid.' . ($activationNote ?? ''),
                ]);
            }

            if ($proofable instanceof StudentPaymentInstallment) {
                if (($proofable->status ?? 'pending') === 'paid') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cicilan ini sudah Paid.',
                    ], 422);
                }

                $paymentProof->update([
                    'status' => 'approved',
                    'admin_note' => $validated['admin_note'] ?? null,
                ]);

                PaymentProof::query()
                    ->where('proofable_type', $paymentProof->proofable_type)
                    ->where('proofable_id', (int)$paymentProof->proofable_id)
                    ->where('id_payment_proof', '<>', (int)$paymentProof->id_payment_proof)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'rejected',
                        'admin_note' => 'Otomatis ditolak: ada bukti lain yang disetujui.',
                    ]);

                $proofable->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $paymentProof->payment_method ?? $proofable->payment_method,
                    // keep latest for backward compatibility
                    'proof_file' => $paymentProof->file_path ?? $proofable->proof_file,
                ]);

                $proofable->load('studentPayment');
                if ($proofable->studentPayment) {
                    $paymentService->syncStudentPaymentStatusFromInstallments($proofable->studentPayment);

                    // Jika parent payment naik jadi 'paid' (semua cicilan lunas),
                    // credit ke fund_sources. Service idempotent jika sudah pernah dicredit.
                    $parent = $proofable->studentPayment->fresh(['payment']);
                    if ($parent && ($parent->status ?? '') === 'paid') {
                        app(FundCreditService::class)->creditFromStudentPayment($parent);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pembayaran cicilan disetujui.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Tipe bukti pembayaran tidak didukung.',
            ], 422);
        });
    }

    public function proofReject(Request $request, PaymentProof $paymentProof)
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:255'],
        ]);

        if (($paymentProof->status ?? 'pending') !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Bukti ini sudah diproses sebelumnya.',
            ], 422);
        }

        $paymentProof->load('proofable');
        $proofable = $paymentProof->proofable;
        if (!$proofable) {
            return response()->json([
                'success' => false,
                'message' => 'Data tagihan/cicilan untuk bukti ini tidak ditemukan.',
            ], 404);
        }

        return DB::transaction(function () use ($paymentProof, $proofable, $validated) {
            if (($paymentProof->status ?? 'pending') !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti ini sudah diproses sebelumnya.',
                ], 422);
            }

            $paymentProof->update([
                'status' => 'rejected',
                'admin_note' => $validated['admin_note'],
            ]);

            // Side effect only for header payments: mark failed so guest sees the state clearly.
            if ($proofable instanceof StudentPayment) {
                if (($proofable->status ?? 'pending') !== 'paid') {
                    $proofable->update([
                        'status' => 'failed',
                        'paid_at' => null,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran ditolak.',
            ]);
        });
    }

    public function update(Request $request, StudentPayment $studentPayment, PaymentService $paymentService)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,failed'],
            'payment_method' => ['nullable', 'in:transfer_bank,e_wallet,cash,qris'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'proof_file' => ['nullable', 'file', 'max:4096'],
        ]);

        // If this payment uses installments, prevent forcing header status to paid
        // while there are still pending installments.
        if ($validated['status'] === 'paid' && $studentPayment->installments()->exists()) {
            $hasPending = $studentPayment->installments()->where('status', 'pending')->exists();
            if ($hasPending) {
                throw ValidationException::withMessages([
                    'status' => 'Tidak bisa set Paid karena masih ada cicilan yang Pending. Silakan lunasi semua cicilan terlebih dahulu.',
                ]);
            }
        }

        $discountAmount = (float)($validated['discount_amount'] ?? $studentPayment->discount_amount ?? 0);

        $snapshot = $studentPayment->detail_fee_snapshot;
        if (!is_array($snapshot)) {
            $snapshot = [];
        }

        $total = $paymentService->computeTotalFromSnapshot($snapshot, (float)($studentPayment->payment?->default_amount ?? 0));
        $final = $total - $discountAmount;
        if ($final < 0) $final = 0;

        $update = [
            'status' => $validated['status'],
            'discount_amount' => $discountAmount,
            'total_amount' => $total,
            'final_amount' => $final,
        ];

        // Preserve current payment_method unless the field is explicitly provided.
        if ($request->exists('payment_method')) {
            $update['payment_method'] = $validated['payment_method'] ?? null;
        }

        if ($request->hasFile('proof_file')) {
            // delete old local proof
            $this->deleteStoredProofIfLocal($studentPayment->proof_file);
            $stored = $request->file('proof_file')->store('payment-proofs/student-payments', 'public');
            $update['proof_file'] = 'storage/' . $stored;
        }

        if ($validated['status'] === 'paid') {
            $update['paid_at'] = now();
        } else {
            $update['paid_at'] = null;
        }

        $studentPayment->update($update);

        $activationNote = '';
        if ($validated['status'] === 'paid') {
            $activationNote = $this->maybeActivateRegistrationAfterRegistrationFeePaid($studentPayment);
        }

        return response()->json(['success' => true, 'message' => 'Tagihan murid berhasil diperbarui.' . ($activationNote ?? '')]);
    }

    private function maybeActivateRegistrationAfterRegistrationFeePaid(StudentPayment $studentPayment): string
    {
        $studentPayment->loadMissing(['payment', 'student']);
        $jenis = $studentPayment->payment?->jenis_payment ?? null;
        $period = (string)($studentPayment->payment_period ?? '');

        if ($jenis !== 'uang_pendaftaran' || $period !== 'ONCE') {
            return '';
        }

        $student = $studentPayment->student;
        $registrationId = (int)($student?->id_registration ?? 0);
        if (!$student || $registrationId <= 0) {
            return '';
        }

        return DB::transaction(function () use ($registrationId, $student) {
            $registration = Registration::query()
                ->where('id_registration', $registrationId)
                ->lockForUpdate()
                ->first();

            if (!$registration) return '';

            if (!in_array(($registration->status ?? 'pending'), ['pending', 'approved_awaiting_payment', 'pending_due'], true)) {
                return '';
            }

            $paidLate = false;
            if ($registration->payment_deadline) {
                $paidLate = now()->toDateString() > $registration->payment_deadline->toDateString();
            }

            $parentsData = $registration->parents_data ?? [];
            if (is_string($parentsData)) {
                $decoded = json_decode($parentsData, true);
                $parentsData = is_array($decoded) ? $decoded : [];
            }

            $fatherPhone = $parentsData['father_phone_num']
                ?? $parentsData['father_phone']
                ?? $parentsData['father_phone_number']
                ?? null;
            $motherPhone = $parentsData['mother_phone_num']
                ?? $parentsData['mother_phone']
                ?? $parentsData['mother_phone_number']
                ?? null;
            $fatherJob = $parentsData['father_occupation']
                ?? $parentsData['father_job']
                ?? null;
            $motherJob = $parentsData['mother_occupation']
                ?? $parentsData['mother_job']
                ?? null;

            $parent = ParentGuardian::query()->where('id_user', $registration->id_user)->first();
            if (!$parent) {
                $parent = ParentGuardian::create([
                    'id_user' => $registration->id_user,
                    'father_name' => $parentsData['father_name'] ?? null,
                    'mother_name' => $parentsData['mother_name'] ?? null,
                    'father_phone_num' => $fatherPhone,
                    'mother_phone_num' => $motherPhone,
                    'father_occupation' => $fatherJob,
                    'mother_occupation' => $motherJob,
                    'father_address' => $parentsData['father_address'] ?? null,
                    'mother_address' => $parentsData['mother_address'] ?? null,
                ]);
            } else {
                $parent->father_name = $parent->father_name ?: ($parentsData['father_name'] ?? null);
                $parent->mother_name = $parent->mother_name ?: ($parentsData['mother_name'] ?? null);
                $parent->father_phone_num = $parent->father_phone_num ?: $fatherPhone;
                $parent->mother_phone_num = $parent->mother_phone_num ?: $motherPhone;
                $parent->father_occupation = $parent->father_occupation ?: $fatherJob;
                $parent->mother_occupation = $parent->mother_occupation ?: $motherJob;
                $parent->father_address = $parent->father_address ?: ($parentsData['father_address'] ?? null);
                $parent->mother_address = $parent->mother_address ?: ($parentsData['mother_address'] ?? null);
                $parent->save();
            }

            if (!$student->id_parents) {
                $student->id_parents = $parent->id_parents;
            }
            if (!$student->id_registration) {
                $student->id_registration = $registration->id_registration;
            }
            $student->group = $student->group ?: ($registration->group ?? null);
            $student->status = 'aktif';
            if (Schema::hasColumn('students', 'paid_late')) {
                $student->paid_late = $paidLate;
            }
            $student->save();

            $registration->status = 'active';
            $registration->paid_late = $paidLate;
            $registration->reject_reason = null;
            $registration->payment_deadline = null;
            $registration->grace_period_until = null;
            $registration->save();

            return ' Pendaftaran diaktifkan.';
        });
    }

    public function destroy(StudentPayment $studentPayment)
    {
        if (($studentPayment->status ?? 'pending') === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan yang sudah paid tidak bisa dihapus.',
            ], 422);
        }

        $this->deleteStoredProofIfLocal($studentPayment->proof_file);
        $studentPayment->delete();

        return response()->json(['success' => true, 'message' => 'Tagihan murid berhasil dihapus']);
    }

    // ===== INSTALLMENTS (MVP - Superadmin) =====
    public function installmentsCreate(StudentPayment $studentPayment)
    {
        $studentPayment->load([
            'student',
            'payment',
            'installments' => fn($q) => $q->orderBy('installment_number'),
        ]);

        return response()->json(['view' => view('components.dashboard.admin.modal-installment-plan', [
            'studentPayment' => $studentPayment,
        ])->render()]);
    }

    public function installmentsStore(Request $request, StudentPayment $studentPayment, PaymentService $paymentService)
    {
        $studentPayment->loadCount('installments');

        if (($studentPayment->status ?? 'pending') === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan sudah Paid. Tidak bisa mengatur cicilan.',
            ], 422);
        }

        if ($studentPayment->installments_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cicilan untuk tagihan ini sudah dibuat. Jika ingin membuat ulang, silakan lakukan Reset Cicilan terlebih dahulu.',
            ], 422);
        }

        $validated = $request->validate([
            'installment_count' => ['required', 'integer', 'min:2', 'max:24'],
            'first_due_date' => ['required', 'date'],
            'interval_months' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $finalAmount = (float)($studentPayment->final_amount ?? 0);
        if ($finalAmount <= 0) {
            throw ValidationException::withMessages([
                'installment_count' => 'Final amount 0. Tidak perlu cicilan.',
            ]);
        }

        $count = (int)$validated['installment_count'];
        $firstDueDate = Carbon::parse($validated['first_due_date'])->startOfDay();
        $intervalMonths = (int)($validated['interval_months'] ?? 1);

        $rows = $paymentService->buildInstallmentSchedule($finalAmount, $count, $firstDueDate, $intervalMonths);

        $studentPayment->installments()->createMany($rows);
        $studentPayment->update([
            'installment_requested' => true,
            'installment_count' => $count,
            'status' => 'pending',
            'paid_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cicilan berhasil dibuat.',
        ]);
    }

    public function installmentsPay(StudentPayment $studentPayment, StudentPaymentInstallment $installment)
    {
        if ((int)$installment->id_student_payment !== (int)$studentPayment->id_student_payment) {
            return response()->json([
                'success' => false,
                'message' => 'Cicilan tidak sesuai dengan tagihan.',
            ], 404);
        }

        $studentPayment->load(['student', 'payment']);

        return response()->json(['view' => view('components.dashboard.admin.modal-installment-pay', [
            'studentPayment' => $studentPayment,
            'installment' => $installment,
        ])->render()]);
    }

    public function installmentsResetConfirm(StudentPayment $studentPayment)
    {
        $studentPayment->load([
            'student',
            'payment',
            'installments' => fn($q) => $q->orderBy('installment_number'),
        ]);

        return response()->json(['view' => view('components.dashboard.admin.modal-installment-reset', [
            'studentPayment' => $studentPayment,
        ])->render()]);
    }

    public function installmentsReset(StudentPayment $studentPayment)
    {
        $studentPayment->load([
            'installments' => fn($q) => $q->orderBy('installment_number'),
        ]);

        $installments = $studentPayment->installments;

        if ($installments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada cicilan untuk di-reset.',
            ], 422);
        }

        $hasPaid = $installments->contains(fn($i) => ($i->status ?? 'pending') === 'paid');
        if ($hasPaid) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa reset cicilan karena ada cicilan yang sudah Paid.',
            ], 422);
        }

        // Delete any stored proofs (should be rare for pending, but handle safely)
        foreach ($installments as $ins) {
            $this->deleteStoredProofIfLocal($ins->proof_file);
        }

        $studentPayment->installments()->delete();
        $studentPayment->update([
            'installment_requested' => false,
            'installment_count' => null,
            'status' => 'pending',
            'paid_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cicilan berhasil di-reset. Silakan atur cicilan kembali.',
        ]);
    }

    public function installmentsPayUpdate(Request $request, StudentPayment $studentPayment, StudentPaymentInstallment $installment, PaymentService $paymentService)
    {
        if ((int)$installment->id_student_payment !== (int)$studentPayment->id_student_payment) {
            return response()->json([
                'success' => false,
                'message' => 'Cicilan tidak sesuai dengan tagihan.',
            ], 404);
        }

        if (($installment->status ?? 'pending') === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cicilan ini sudah Paid.',
            ], 422);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:transfer_bank,e_wallet,cash,qris'],
            'proof_file' => ['nullable', 'file', 'max:4096'],
        ]);

        $update = [
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $validated['payment_method'],
        ];

        if ($request->hasFile('proof_file')) {
            $this->deleteStoredProofIfLocal($installment->proof_file);
            $stored = $request->file('proof_file')->store('payment-proofs/installments', 'public');
            $update['proof_file'] = 'storage/' . $stored;
        }

        $installment->update($update);

        $paymentService->syncStudentPaymentStatusFromInstallments($studentPayment);

        return response()->json([
            'success' => true,
            'message' => 'Cicilan berhasil dibayar.',
        ]);
    }

    public function export(Request $request)
    {
        $query = StudentPayment::query()->with(['student', 'payment']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_period', 'like', "%{$search}%")
                    ->orWhereHas('student', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('payment', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('id_payment') && $request->input('id_payment') !== 'all') {
            $query->where('id_payment', (int)$request->input('id_payment'));
        }

        $rows = $query->orderByDesc('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="student_payments_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Murid', 'Payment', 'Periode', 'Total', 'Diskon', 'Final', 'Status', 'Metode', 'Paid At']);

            foreach ($rows as $sp) {
                fputcsv($file, [
                    $sp->id_student_payment,
                    $sp->student?->name ?? '-',
                    $sp->payment?->name ?? '-',
                    $sp->payment_period,
                    (string)($sp->total_amount ?? 0),
                    (string)($sp->discount_amount ?? 0),
                    (string)($sp->final_amount ?? 0),
                    $sp->status,
                    $sp->payment_method ?? '',
                    $sp->paid_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function normalizePaymentPeriod(string $mode, ?string $periodInput): string
    {
        $mode = $mode ?: 'one_time';

        if ($mode === 'one_time') {
            return 'ONCE';
        }

        $periodInput = trim((string)($periodInput ?? ''));

        if ($mode === 'monthly') {
            if (!preg_match('/^\d{4}-\d{2}$/', $periodInput)) {
                throw ValidationException::withMessages([
                    'payment_period' => 'Periode bulanan harus format YYYY-MM (contoh: 2026-07).',
                ]);
            }
            $month = (int)substr($periodInput, 5, 2);
            if ($month < 1 || $month > 12) {
                throw ValidationException::withMessages([
                    'payment_period' => 'Bulan pada periode harus 01-12.',
                ]);
            }
            return $periodInput;
        }

        if ($mode === 'school_year') {
            if (!preg_match('/^\d{4}\/\d{4}$/', $periodInput)) {
                throw ValidationException::withMessages([
                    'payment_period' => 'Tahun ajaran harus format YYYY/YYYY (contoh: 2026/2027).',
                ]);
            }
            [$y1, $y2] = array_map('intval', explode('/', $periodInput));
            if ($y2 !== ($y1 + 1)) {
                throw ValidationException::withMessages([
                    'payment_period' => 'Tahun ajaran harus berurutan (contoh: 2026/2027).',
                ]);
            }
            return $periodInput;
        }

        // Unknown mode fallback
        return $periodInput !== '' ? $periodInput : 'ONCE';
    }

    private function deleteStoredProofIfLocal(?string $path): void
    {
        $path = (string)($path ?? '');
        if ($path === '') return;

        // Stored as "storage/<relative>". Convert back to disk relative.
        if (str_starts_with($path, 'storage/')) {
            $relative = substr($path, strlen('storage/'));
            Storage::disk('public')->delete($relative);
        }
    }

    private function sendStudentPaymentCreatedEmail(StudentPayment $studentPayment): void
    {
        $studentPayment->loadMissing([
            'student.registration.user',
            'student.parent.user',
            'payment',
        ]);

        $recipientUser = $studentPayment->student?->registration?->user
            ?? $studentPayment->student?->parent?->user;

        $recipientEmail = $recipientUser?->email;
        if (!$recipientEmail) {
            return;
        }

        try {
            Mail::to($recipientEmail)->send(new StudentPaymentCreated($studentPayment, $recipientUser));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
