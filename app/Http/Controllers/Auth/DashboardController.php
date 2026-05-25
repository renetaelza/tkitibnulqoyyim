<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentPayment;
use App\Models\StudentPaymentInstallment;
use App\Models\PaymentProof;
use App\Models\PaymentMethod;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Show dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();

        $roleRaw = (string)($user?->role ?? '');
        $role = $roleRaw === 'super_admin' ? 'superadmin' : $roleRaw;

        // Staff roles use the staff portal (/admin). Keep /dashboard as guest portal.
        if (in_array($role, ['superadmin', 'administration', 'teacher', 'headmaster'], true)) {
            return redirect()->route('admin.dashboard');
        }
        
        $data = [
            'user' => $user,
            'role' => $role,
            'greeting' => $this->getGreeting($user),
            'stats' => $this->getStats($user),
            'activities' => $this->getActivities($user),
        ];

        // Prepare guest-specific data if user is guest
        if ($user->role === 'guest') {
            $data = array_merge($data, $this->buildGuestDashboardData($user->id));
        }

        return view('dashboard.index', $data);
    }

    public function guestInfo(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'guest') {
            return redirect()->route('admin.dashboard');
        }

        $data = $this->buildGuestDashboardData($user->id);

        return view('dashboard.guest.info', $data);
    }

    public function guestBills(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'guest') {
            return redirect()->route('admin.dashboard');
        }

        $data = $this->buildGuestDashboardData($user->id);

        return view('dashboard.guest.bills', $data);
    }

    public function guestStudents(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'guest') {
            return redirect()->route('admin.dashboard');
        }

        $attendanceWindow = (int)$request->query('attendance_window', 60);
        $allowedWindows = [30, 60, 90, 180];
        if (!in_array($attendanceWindow, $allowedWindows, true)) {
            $attendanceWindow = 60;
        }

        $data = $this->buildGuestDashboardData($user->id, true, $attendanceWindow);

        $selectedStudentId = (int)$request->query('student_id', 0);
        if ($selectedStudentId > 0) {
            $hasStudent = collect($data['students'] ?? [])
                ->contains(fn($student) => (int)($student?->id_student ?? 0) === $selectedStudentId);
            if (!$hasStudent) {
                $selectedStudentId = 0;
            }
        }

        $data['selectedStudentId'] = $selectedStudentId;
        if ($selectedStudentId > 0) {
            $data['studentOverview'] = collect($data['studentOverview'] ?? [])
                ->filter(fn($row) => (int)($row['student']?->id_student ?? 0) === $selectedStudentId)
                ->values();
        }

        return view('dashboard.guest.students', $data);
    }

    public function guestBillsPay(Request $request, StudentPayment $studentPayment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'guest') {
            return redirect()->route('admin.dashboard');
        }

        $studentPayment->load(['student.registration', 'payment']);
        $ownerId = $studentPayment->student?->registration?->id_user;
        if ((int)$ownerId !== (int)$user->id) {
            abort(403, 'Unauthorized');
        }

        $regStatus = (string)($studentPayment->student?->registration?->status ?? '');
        $jenis = (string)($studentPayment->payment?->jenis_payment ?? '');
        if ($jenis === 'uang_pendaftaran') {
            if (!in_array($regStatus, ['approved_awaiting_payment', 'pending_due'], true)) {
                return redirect()->route('dashboard.bills')->with('error', 'Pembayaran uang pendaftaran hanya bisa dilakukan setelah pendaftaran di-approve (menunggu pembayaran).');
            }
        } else {
            if ($regStatus !== 'active') {
                return redirect()->route('dashboard.bills')->with('error', 'Pembayaran tagihan hanya bisa dilakukan setelah pendaftaran aktif.');
            }
        }

        if (($studentPayment->status ?? 'pending') === 'paid') {
            return redirect()->route('dashboard.bills')->with('error', 'Tagihan sudah Lunas.');
        }

        $hasPendingProof = PaymentProof::query()
            ->where('proofable_type', StudentPayment::class)
            ->where('proofable_id', (int)$studentPayment->id_student_payment)
            ->where('status', 'pending')
            ->exists();
        if ($hasPendingProof) {
            return redirect()->route('dashboard.bills')->with('error', 'Masih ada bukti pembayaran yang menunggu verifikasi admin. Silakan tunggu hasil verifikasi sebelum mengirim bukti baru.');
        }

        if ($studentPayment->installments()->exists()) {
            return redirect()->route('dashboard.bills')->with('error', 'Tagihan ini menggunakan cicilan. Silakan upload bukti pada cicilan yang tersedia.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:transfer_bank,e_wallet,cash,qris'],
            'payment_method_item_id' => ['nullable', 'integer'],
            'proof_file' => ['required', 'file', 'max:4096', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $methodSnapshot = [
            'payment_method_label' => null,
            'payment_method_account_number' => null,
            'payment_method_account_name' => null,
        ];

        if (Schema::hasTable('payment_methods') && in_array($validated['payment_method'], ['transfer_bank', 'e_wallet'], true)) {
            $requiredType = $validated['payment_method'] === 'transfer_bank' ? 'bank' : 'ewallet';
            $hasAny = PaymentMethod::query()->where('type', $requiredType)->where('is_active', true)->exists();

            $selectedId = (int)($validated['payment_method_item_id'] ?? 0);
            if ($hasAny && $selectedId <= 0) {
                return redirect()->route('dashboard.bills')->with('error', 'Silakan pilih ' . ($requiredType === 'bank' ? 'bank' : 'provider e-wallet') . ' tujuan.');
            }

            if ($selectedId > 0) {
                $pm = PaymentMethod::query()->where('id', $selectedId)->where('is_active', true)->first();
                if (!$pm || (string)($pm->type ?? '') !== $requiredType) {
                    return redirect()->route('dashboard.bills')->with('error', 'Pilihan metode pembayaran tidak valid.');
                }

                $methodSnapshot = [
                    'payment_method_label' => $pm->label ?? null,
                    'payment_method_account_number' => $pm->account_number ?? null,
                    'payment_method_account_name' => $pm->account_name ?? null,
                ];
            }
        }

        if ($request->hasFile('proof_file')) {
            $stored = $request->file('proof_file')->store('payment-proofs/guest/student-payments', 'public');

            $publicPath = 'storage/' . $stored;

            // Append proof history (do not delete/overwrite old proofs)
            $proofPayload = [
                'proofable_type' => StudentPayment::class,
                'proofable_id' => (int)$studentPayment->id_student_payment,
                'uploaded_by_user_id' => (int)$user->id,
                'payment_method' => $validated['payment_method'],
                'file_path' => $publicPath,
                'status' => 'pending',
            ];

            if (Schema::hasColumn('payment_proofs', 'payment_method_label')) {
                $proofPayload['payment_method_label'] = $methodSnapshot['payment_method_label'];
            }
            if (Schema::hasColumn('payment_proofs', 'payment_method_account_number')) {
                $proofPayload['payment_method_account_number'] = $methodSnapshot['payment_method_account_number'];
            }
            if (Schema::hasColumn('payment_proofs', 'payment_method_account_name')) {
                $proofPayload['payment_method_account_name'] = $methodSnapshot['payment_method_account_name'];
            }

            PaymentProof::create($proofPayload);

            $studentPayment->update([
                'payment_method' => $validated['payment_method'],
                // keep latest for backward compatibility
                'proof_file' => $publicPath,
                // If previously failed, allow re-verification.
                'status' => ($studentPayment->status ?? 'pending') === 'failed' ? 'pending' : ($studentPayment->status ?? 'pending'),
            ]);
        }

        return redirect()->route('dashboard.bills')->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
    }

    public function guestBillsInstallmentPay(Request $request, StudentPayment $studentPayment, StudentPaymentInstallment $installment): RedirectResponse
    {
        $user = Auth::user();
        if (!$user || ($user->role ?? null) !== 'guest') {
            return redirect()->route('admin.dashboard');
        }

        if ((int)($installment->id_student_payment ?? 0) !== (int)($studentPayment->id_student_payment ?? 0)) {
            abort(404);
        }

        $studentPayment->load(['student.registration', 'payment']);
        $ownerId = $studentPayment->student?->registration?->id_user;
        if ((int)$ownerId !== (int)$user->id) {
            abort(403, 'Unauthorized');
        }

        $regStatus = (string)($studentPayment->student?->registration?->status ?? '');
        if ($regStatus !== 'active') {
            return redirect()->route('dashboard.bills')->with('error', 'Pembayaran tagihan hanya bisa dilakukan setelah pendaftaran aktif.');
        }

        if (($installment->status ?? 'pending') === 'paid') {
            return redirect()->route('dashboard.bills')->with('error', 'Cicilan ini sudah Lunas.');
        }

        $hasPendingProof = PaymentProof::query()
            ->where('proofable_type', StudentPaymentInstallment::class)
            ->where('proofable_id', (int)$installment->id_student_payment_installment)
            ->where('status', 'pending')
            ->exists();
        if ($hasPendingProof) {
            return redirect()->route('dashboard.bills')->with('error', 'Masih ada bukti cicilan yang menunggu verifikasi admin. Silakan tunggu hasil verifikasi sebelum mengirim bukti baru.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:transfer_bank,e_wallet,cash,qris'],
            'payment_method_item_id' => ['nullable', 'integer'],
            'proof_file' => ['required', 'file', 'max:4096', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $methodSnapshot = [
            'payment_method_label' => null,
            'payment_method_account_number' => null,
            'payment_method_account_name' => null,
        ];

        if (Schema::hasTable('payment_methods') && in_array($validated['payment_method'], ['transfer_bank', 'e_wallet'], true)) {
            $requiredType = $validated['payment_method'] === 'transfer_bank' ? 'bank' : 'ewallet';
            $hasAny = PaymentMethod::query()->where('type', $requiredType)->where('is_active', true)->exists();

            $selectedId = (int)($validated['payment_method_item_id'] ?? 0);
            if ($hasAny && $selectedId <= 0) {
                return redirect()->route('dashboard.bills')->with('error', 'Silakan pilih ' . ($requiredType === 'bank' ? 'bank' : 'provider e-wallet') . ' tujuan.');
            }

            if ($selectedId > 0) {
                $pm = PaymentMethod::query()->where('id', $selectedId)->where('is_active', true)->first();
                if (!$pm || (string)($pm->type ?? '') !== $requiredType) {
                    return redirect()->route('dashboard.bills')->with('error', 'Pilihan metode pembayaran tidak valid.');
                }

                $methodSnapshot = [
                    'payment_method_label' => $pm->label ?? null,
                    'payment_method_account_number' => $pm->account_number ?? null,
                    'payment_method_account_name' => $pm->account_name ?? null,
                ];
            }
        }

        if ($request->hasFile('proof_file')) {
            $stored = $request->file('proof_file')->store('payment-proofs/guest/student-payment-installments', 'public');

            $publicPath = 'storage/' . $stored;

            $proofPayload = [
                'proofable_type' => StudentPaymentInstallment::class,
                'proofable_id' => (int)$installment->id_student_payment_installment,
                'uploaded_by_user_id' => (int)$user->id,
                'payment_method' => $validated['payment_method'],
                'file_path' => $publicPath,
                'status' => 'pending',
            ];

            if (Schema::hasColumn('payment_proofs', 'payment_method_label')) {
                $proofPayload['payment_method_label'] = $methodSnapshot['payment_method_label'];
            }
            if (Schema::hasColumn('payment_proofs', 'payment_method_account_number')) {
                $proofPayload['payment_method_account_number'] = $methodSnapshot['payment_method_account_number'];
            }
            if (Schema::hasColumn('payment_proofs', 'payment_method_account_name')) {
                $proofPayload['payment_method_account_name'] = $methodSnapshot['payment_method_account_name'];
            }

            PaymentProof::create($proofPayload);

            $installment->update([
                'payment_method' => $validated['payment_method'],
                // keep latest for backward compatibility
                'proof_file' => $publicPath,
            ]);
        }

        return redirect()->route('dashboard.bills')->with('success', 'Bukti pembayaran cicilan berhasil dikirim. Menunggu verifikasi admin.');
    }

    private function deleteLocalProofFile(?string $proofPath): void
    {
        $path = trim((string)($proofPath ?? ''));
        if ($path === '') return;

        // We store files as "storage/<path>" for public access.
        if (!str_starts_with($path, 'storage/')) {
            return;
        }

        $relative = substr($path, strlen('storage/'));
        if ($relative === '') return;

        try {
            Storage::disk('public')->delete($relative);
        } catch (\Throwable $_) {
            // ignore
        }
    }

    private function buildGuestDashboardData(int $userId, bool $withDetails = false, ?int $attendanceWindowDays = null): array
    {
        $data = [];
        $data['currentStep'] = session('current_step', 1);
        $attendanceWindowDays = $attendanceWindowDays ?: 60;
        $data['attendanceWindowDays'] = $attendanceWindowDays;

        $approvedRegistration = Registration::where('id_user', $userId)
            ->where('status', 'active')
            ->latest('id_registration')
            ->first();

        $pendingRegistration = Registration::where('id_user', $userId)
            ->whereIn('status', ['pending', 'rejected', 'approved_awaiting_payment', 'pending_due'])
            ->latest('id_registration')
            ->first();

        $data['approvedRegistration'] = $approvedRegistration;
        $data['pendingRegistration'] = $pendingRegistration;
        $data['studentInfo'] = null;
        $data['hasChild'] = false;
        $data['hasStudent'] = false;
        $data['studentPayments'] = collect();
        $data['studentBillGroups'] = collect();
        $data['studentSummaries'] = collect();
        $data['announcements'] = [];
        $data['billSummary'] = [
            'student_count' => 0,
            'total_bills' => 0,
            'pending_bills' => 0,
            'waiting_verification' => 0,
            'failed_bills' => 0,
            'outstanding_amount' => 0,
        ];
        $data['paymentMethods'] = Schema::hasTable('payment_methods')
            ? PaymentMethod::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        // Legacy fallback (single row), kept for backward compatibility.
        $data['paymentSettings'] = Schema::hasTable('payment_settings')
            ? PaymentSetting::query()->first()
            : null;

        $registrationIds = Registration::query()
            ->where('id_user', $userId)
            ->orderByDesc('id_registration')
            ->pluck('id_registration');

        $students = $registrationIds->isNotEmpty()
            ? Student::query()
                ->whereIn('id_registration', $registrationIds)
                ->orderBy('name')
                ->get()
            : collect();

        $data['students'] = $students;
        $data['hasStudent'] = $students->isNotEmpty();
        $data['hasChild'] = $students->isNotEmpty();

        // If approved, get student information
        if ($approvedRegistration) {
            $student = Student::where('id_registration', $approvedRegistration->id_registration)->first();

            if ($student) {
                $genderValue = $student->gender ?? null;
                $genderNormalized = $genderValue === 'pria'
                    ? 'male'
                    : ($genderValue === 'perempuan' ? 'female' : $genderValue);

                $statusValue = $student->status ?? null;
                $statusNormalized = $statusValue === 'aktif'
                    ? 'active'
                    : ($statusValue === 'non-aktif' ? 'inactive' : $statusValue);

                $data['studentInfo'] = [
                    'id_student' => $student->id_student,
                    'name' => $student->name,
                    'birth_date' => $student->birth_date,
                    'gender' => $genderNormalized,
                    'group' => $approvedRegistration->group,
                    'status' => $statusNormalized,
                ];
            }
        }

        // Load bills for all students under this user (supports multi-child parents).
        if ($students->isNotEmpty()) {
            $studentPayments = StudentPayment::query()
                ->whereIn('id_student', $students->pluck('id_student'))
                ->with([
                    'student',
                    'payment',
                    'proofs' => fn($q) => $q->latest('id_payment_proof'),
                    'installments' => fn($q) => $q->orderBy('installment_number'),
                    'installments.proofs' => fn($q) => $q->latest('id_payment_proof'),
                ])
                ->orderByDesc('created_at')
                ->get();

            $groupedPayments = $studentPayments->groupBy('id_student');

            $paymentsByStudent = $studentPayments->groupBy('id_student');

            $latestAttendanceByStudent = collect();
            if (Schema::hasTable('student_attendance')) {
                $attendanceRows = StudentAttendance::query()
                    ->whereIn('id_student', $students->pluck('id_student'))
                    ->orderByDesc('date')
                    ->orderByDesc('id_attendance')
                    ->get(['id_student', 'date', 'status', 'information']);

                $latestAttendanceByStudent = $attendanceRows
                    ->groupBy('id_student')
                    ->map(fn($rows) => $rows->first());
            }

            $studentSummaries = $students->map(function ($student) use ($paymentsByStudent, $latestAttendanceByStudent) {
                $payments = $paymentsByStudent->get($student->id_student, collect());
                $pendingCount = $payments->filter(fn($p) => (string)($p->status ?? 'pending') !== 'paid')->count();

                $statusValue = (string)($student->status ?? 'pending_payment');
                $statusLabel = match ($statusValue) {
                    'aktif', 'active' => 'Aktif',
                    'non-aktif', 'inactive' => 'Nonaktif',
                    'pending_payment' => 'Belum Aktif',
                    'rejected' => 'Ditolak',
                    default => $statusValue,
                };

                $latestAttendance = $latestAttendanceByStudent->get($student->id_student);
                $latestAttendanceLabel = $latestAttendance
                    ? match ((string)($latestAttendance->status ?? '')) {
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'alpa' => 'Alpa',
                        default => '-'
                    }
                    : '-';

                $latestAttendanceDate = $latestAttendance?->date?->format('Y-m-d') ?? '-';

                return [
                    'id_student' => $student->id_student,
                    'name' => $student->name,
                    'group' => $student->group,
                    'status_label' => $statusLabel,
                    'pending_bills' => $pendingCount,
                    'latest_attendance' => $latestAttendanceLabel,
                    'latest_attendance_date' => $latestAttendanceDate,
                ];
            });

            $failedCount = $studentPayments->where('status', 'failed')->count();
            $pendingNoProofCount = $studentPayments->filter(function ($sp) {
                $statusValue = (string)($sp->status ?? 'pending');
                if ($statusValue === 'paid') return false;
                $installmentTotal = collect($sp->installments ?? [])->count();
                $proofs = collect($sp->proofs ?? []);
                $hasProof = $proofs->count() > 0 || (bool)($sp->proof_file ?? null);
                return $installmentTotal === 0 && !$hasProof;
            })->count();

            $pendingWithProofCount = $studentPayments->filter(function ($sp) {
                $statusValue = (string)($sp->status ?? 'pending');
                $installmentTotal = collect($sp->installments ?? [])->count();
                $proofs = collect($sp->proofs ?? []);
                $hasProof = $proofs->count() > 0 || (bool)($sp->proof_file ?? null);
                return $installmentTotal === 0 && $statusValue !== 'paid' && $hasProof;
            })->count();

            $installmentPendingWithProofCount = $studentPayments->reduce(function ($carry, $sp) {
                $installments = collect($sp->installments ?? []);
                $count = $installments->filter(function ($i) {
                    $statusValue = (string)($i->status ?? 'pending');
                    $proofs = collect($i->proofs ?? []);
                    $hasProof = $proofs->count() > 0 || (bool)($i->proof_file ?? null);
                    return $statusValue !== 'paid' && $hasProof;
                })->count();
                return $carry + $count;
            }, 0);

            $outstandingAmount = $studentPayments
                ->filter(fn($sp) => (string)($sp->status ?? 'pending') !== 'paid')
                ->sum(fn($sp) => (float)($sp->final_amount ?? 0));

            $data['studentSummaries'] = $studentSummaries;
            $data['billSummary'] = [
                'student_count' => $students->count(),
                'total_bills' => $studentPayments->count(),
                'pending_bills' => $pendingNoProofCount,
                'waiting_verification' => $pendingWithProofCount + $installmentPendingWithProofCount,
                'failed_bills' => $failedCount,
                'outstanding_amount' => $outstandingAmount,
            ];

            $data['studentPayments'] = $studentPayments;
            $data['studentBillGroups'] = $students->map(function ($student) use ($groupedPayments) {
                return [
                    'student' => $student,
                    'payments' => $groupedPayments->get($student->id_student, collect()),
                ];
            })->values();

            if ($withDetails) {
                $data['announcements'] = [
                    [
                        'title' => 'Pertemuan Orang Tua',
                        'tag' => 'Info',
                        'date' => now()->format('Y-m-d'),
                        'body' => 'Mohon hadir pada pertemuan wali murid pekan ini untuk update kurikulum semester berjalan.',
                    ],
                    [
                        'title' => 'Kegiatan Sekolah',
                        'tag' => 'Agenda',
                        'date' => now()->addDays(3)->format('Y-m-d'),
                        'body' => 'Minggu depan ada kegiatan tematik dan kunjungan edukasi. Informasi detail akan menyusul.',
                    ],
                    [
                        'title' => 'Pengingat Administrasi',
                        'tag' => 'Tagihan',
                        'date' => now()->format('Y-m-d'),
                        'body' => 'Harap cek kembali tagihan yang masih berjalan agar proses administrasi tetap lancar.',
                    ],
                ];

                $attendanceWindowRows = collect();
                if (Schema::hasTable('student_attendance')) {
                    $attendanceWindowRows = StudentAttendance::query()
                        ->whereIn('id_student', $students->pluck('id_student'))
                        ->where('date', '>=', now()->subDays($attendanceWindowDays)->toDateString())
                        ->orderByDesc('date')
                        ->orderByDesc('id_attendance')
                        ->get(['id_student', 'date', 'status', 'information']);
                }

                $attendanceByStudent = $attendanceWindowRows->groupBy('id_student');
                $paymentsByStudent = $studentPayments->groupBy('id_student');

                $data['studentOverview'] = $students->map(function ($student) use ($attendanceByStudent, $paymentsByStudent) {
                    $attendanceRows = $attendanceByStudent->get($student->id_student, collect());
                    $attendanceSummary = [
                        'hadir' => $attendanceRows->where('status', 'hadir')->count(),
                        'izin' => $attendanceRows->where('status', 'izin')->count(),
                        'sakit' => $attendanceRows->where('status', 'sakit')->count(),
                        'alpa' => $attendanceRows->where('status', 'alpa')->count(),
                    ];

                    $recentAttendance = $attendanceRows->take(6)->map(function ($row) {
                        $statusLabel = match ((string)($row->status ?? '')) {
                            'hadir' => 'Hadir',
                            'izin' => 'Izin',
                            'sakit' => 'Sakit',
                            'alpa' => 'Alpa',
                            default => '-'
                        };

                        return [
                            'date' => $row->date?->format('Y-m-d') ?? '-',
                            'status' => (string)($row->status ?? ''),
                            'status_label' => $statusLabel,
                            'information' => (string)($row->information ?? ''),
                        ];
                    })->values();

                    $payments = $paymentsByStudent->get($student->id_student, collect());
                    $pendingPayments = $payments
                        ->filter(fn($p) => (string)($p->status ?? 'pending') !== 'paid')
                        ->take(6)
                        ->map(function ($p) {
                            $statusValue = (string)($p->status ?? 'pending');
                            $statusLabel = match ($statusValue) {
                                'pending' => 'Menunggu',
                                'failed' => 'Gagal',
                                default => $statusValue,
                            };
                            return [
                                'payment_name' => $p->payment?->name ?? '-',
                                'period' => $p->payment_period ?? '-',
                                'amount_label' => 'Rp ' . number_format((float)($p->final_amount ?? 0), 0, ',', '.'),
                                'status_label' => $statusLabel,
                                'status' => $statusValue,
                            ];
                        })
                        ->values();

                    $paidPayments = $payments
                        ->filter(fn($p) => (string)($p->status ?? 'pending') === 'paid')
                        ->sortByDesc(fn($p) => $p->paid_at ?? $p->updated_at ?? $p->created_at)
                        ->take(6)
                        ->map(function ($p) {
                            $paidAt = $p->paid_at ? $p->paid_at->format('Y-m-d') : '-';
                            return [
                                'payment_name' => $p->payment?->name ?? '-',
                                'period' => $p->payment_period ?? '-',
                                'amount_label' => 'Rp ' . number_format((float)($p->final_amount ?? 0), 0, ',', '.'),
                                'paid_at' => $paidAt,
                            ];
                        })
                        ->values();

                    $issues = [];
                    if ($attendanceSummary['alpa'] > 0) {
                        $issues[] = $attendanceSummary['alpa'] . ' hari alpa dalam ' . $attendanceWindowDays . ' hari terakhir.';
                    }
                    if ($attendanceSummary['sakit'] > 0) {
                        $issues[] = $attendanceSummary['sakit'] . ' hari sakit dalam ' . $attendanceWindowDays . ' hari terakhir.';
                    }
                    $pendingCount = $payments->filter(fn($p) => (string)($p->status ?? 'pending') !== 'paid')->count();
                    if ($pendingCount > 0) {
                        $issues[] = $pendingCount . ' tagihan belum lunas.';
                    }

                    $genderValue = $student->gender ?? null;
                    $genderLabel = match ($genderValue) {
                        'pria' => 'Laki-laki',
                        'perempuan' => 'Perempuan',
                        default => $genderValue ?: '-',
                    };

                    $statusValue = (string)($student->status ?? 'pending_payment');
                    $statusLabel = match ($statusValue) {
                        'aktif', 'active' => 'Aktif',
                        'non-aktif', 'inactive' => 'Nonaktif',
                        'pending_payment' => 'Belum Aktif',
                        'rejected' => 'Ditolak',
                        default => $statusValue,
                    };

                    return [
                        'student' => $student,
                        'status_label' => $statusLabel,
                        'gender_label' => $genderLabel,
                        'birth_date' => $student->birth_date?->format('Y-m-d') ?? '-',
                        'attendance_summary' => $attendanceSummary,
                        'recent_attendance' => $recentAttendance,
                        'pending_payments' => $pendingPayments,
                        'paid_payments' => $paidPayments,
                        'issues' => $issues,
                    ];
                })->values();
            }
        }

        return $data;
    }

    /**
     * Get greeting based on time
     */
    private function getGreeting($user)
    {
        $hour = now()->hour;
        
        if ($hour < 12) {
            return 'Selamat Pagi';
        } elseif ($hour < 17) {
            return 'Selamat Siang';
        } else {
            return 'Selamat Malam';
        }
    }

    /**
     * Get statistics based on user role
     */
    private function getStats($user)
    {
        $stats = [];

        switch ($user->role) {
            case 'superadmin':
                $stats = [
                    ['icon' => '👥', 'value' => '5', 'label' => 'Total Users'],
                    ['icon' => '👨‍🏫', 'value' => '10', 'label' => 'Total Teachers'],
                    ['icon' => '📚', 'value' => '15', 'label' => 'Total Classes'],
                    ['icon' => '📊', 'value' => '95%', 'label' => 'System Health'],
                ];
                break;

            case 'administration':
                $stats = [
                    ['icon' => '📝', 'value' => '12', 'label' => 'Pending Registrations'],
                    ['icon' => '✅', 'value' => '8', 'label' => 'Approved Today'],
                    ['icon' => '👨‍🎓', 'value' => '25', 'label' => 'New Students'],
                    ['icon' => '⚠️', 'value' => '3', 'label' => 'Incomplete Applications'],
                ];
                break;

            case 'teacher':
                $stats = [
                    ['icon' => '👥', 'value' => '28', 'label' => 'Jumlah Siswa'],
                    ['icon' => '📚', 'value' => '5', 'label' => 'Kelas Diajar'],
                    ['icon' => '📝', 'value' => '12', 'label' => 'Tugas Diberikan'],
                    ['icon' => '⭐', 'value' => '4.8', 'label' => 'Rating'],
                ];
                break;

            case 'headmaster':
                $stats = [
                    ['icon' => '👥', 'value' => '285', 'label' => 'Total Siswa'],
                    ['icon' => '👨‍🏫', 'value' => '22', 'label' => 'Total Guru'],
                    ['icon' => '📚', 'value' => '12', 'label' => 'Total Kelas'],
                    ['icon' => '📊', 'value' => '98%', 'label' => 'Tingkat Kehadiran'],
                ];
                break;

            default: // guest
                $stats = [
                    ['icon' => '📚', 'value' => 'TK', 'label' => 'Jenis Sekolah'],
                    ['icon' => '🏆', 'value' => '12+', 'label' => 'Tahun Berpengalaman'],
                    ['icon' => '👥', 'value' => '300+', 'label' => 'Alumni Sukses'],
                    ['icon' => '⭐', 'value' => '5/5', 'label' => 'Rating'],
                ];
                break;
        }

        return $stats;
    }

    /**
     * Get activities/todos based on role
     */
    private function getActivities($user)
    {
        $activities = [];

        switch ($user->role) {
            case 'superadmin':
                $activities = [
                    ['icon' => '🔧', 'text' => 'Check system logs', 'time' => '1 jam lalu'],
                    ['icon' => '👤', 'text' => 'Add new administrator', 'time' => '3 jam lalu'],
                    ['icon' => '🔐', 'text' => 'System backup completed', 'time' => '6 jam lalu'],
                ];
                break;

            case 'administration':
                $activities = [
                    ['icon' => '✍️', 'text' => 'Review 3 new registrations', 'time' => 'pending'],
                    ['icon' => '✅', 'text' => 'Approve student documents', 'time' => '1 jam lalu'],
                    ['icon' => '📧', 'text' => 'Send notification to parents', 'time' => '2 jam lalu'],
                ];
                break;

            case 'teacher':
                $activities = [
                    ['icon' => '📝', 'text' => 'Grade assignments - Class A', 'time' => 'pending'],
                    ['icon' => '📢', 'text' => 'Post class announcement', 'time' => '1 jam lalu'],
                    ['icon' => '👥', 'text' => 'Update attendance report', 'time' => '2 jam lalu'],
                ];
                break;

            case 'headmaster':
                $activities = [
                    ['icon' => '📊', 'text' => 'Review monthly report', 'time' => 'pending'],
                    ['icon' => '👥', 'text' => 'Schedule staff meeting', 'time' => '1 jam lalu'],
                    ['icon' => '📋', 'text' => 'Approve budget allocation', 'time' => '3 jam lalu'],
                ];
                break;

            default: // guest
                $activities = [
                    ['icon' => '📖', 'text' => 'Pelajari program sekolah', 'time' => 'tersedia'],
                    ['icon' => '📞', 'text' => 'Hubungi pihak sekolah', 'time' => 'tersedia'],
                    ['icon' => '📝', 'text' => 'Daftar siswa baru', 'time' => 'buka hingga 30 April'],
                ];
                break;
        }

        return $activities;
    }
}
