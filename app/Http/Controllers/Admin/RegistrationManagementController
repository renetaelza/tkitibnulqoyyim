<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistrationManagementController extends Controller
{
    /**
     * Display a listing of registrations with search and filter
     */
    public function index(Request $request)
    {
        $query = Registration::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('group', 'like', "%{$search}%")
                    ->orWhere('candidate_data->name', 'like', "%{$search}%")
                    ->orWhere('parents_data->father_name', 'like', "%{$search}%")
                    ->orWhere('parents_data->mother_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'pending') {
                // Backward-compat: treat legacy payment-related statuses as pending actions.
                $query->whereIn('status', ['pending', 'approved_awaiting_payment', 'pending_due']);
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by group
        if ($request->filled('group') && $request->input('group') !== 'all') {
            $query->where('group', $request->input('group'));
        }

        // Sort
        $sortBy = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $registrations = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.registrations', [
            'registrations' => $registrations,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
            'group' => $request->input('group', 'all'),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new registration
     */
    public function create()
    {
        $users = User::where('role', 'guest')->get();

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'registration',
            'action' => 'create',
            'registration' => null,
            'users' => $users,
        ])->render()]);
    }

    /**
     * Store a newly created registration in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id',
            'candidate_name' => 'required|string|max:255',
            'candidate_birth_place' => 'nullable|string|max:255',
            'candidate_birth_date' => 'nullable|date',
            'candidate_gender' => 'nullable|in:pria,perempuan',

            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'father_phone_num' => 'nullable|string|max:50',
            'mother_phone_num' => 'nullable|string|max:50',
            'father_occupation' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'father_address' => 'nullable|string|max:2000',
            'mother_address' => 'nullable|string|max:2000',

            'group' => 'nullable|string|max:50',
        ]);

        $registration = new Registration();
        $registration->id_user = $validated['id_user'];
        $registration->group = $validated['group'] ?? null;
        // Always create as pending. Deadline/grace are auto-filled when admin approves.
        $registration->status = 'pending';
        $registration->payment_deadline = null;
        $registration->grace_period_until = null;
        $registration->paid_late = false;
        $registration->reject_reason = null;

        $registration->candidate_data = [
            'name' => $validated['candidate_name'],
            'birth_place' => $validated['candidate_birth_place'] ?? null,
            'birth_date' => $validated['candidate_birth_date'] ?? null,
            'gender' => $validated['candidate_gender'] ?? null,
        ];

        $registration->parents_data = [
            'father_name' => $validated['father_name'] ?? null,
            'mother_name' => $validated['mother_name'] ?? null,
            'father_phone_num' => $validated['father_phone_num'] ?? null,
            'mother_phone_num' => $validated['mother_phone_num'] ?? null,
            'father_occupation' => $validated['father_occupation'] ?? null,
            'mother_occupation' => $validated['mother_occupation'] ?? null,
            'father_address' => $validated['father_address'] ?? null,
            'mother_address' => $validated['mother_address'] ?? null,
        ];

        $registration->save();

        return response()->json(['success' => true, 'message' => 'Pendaftaran berhasil ditambahkan']);
    }

    /**
     * Show the form for editing the specified registration
     */
    public function edit(Registration $registration)
    {
        $users = User::where('role', 'guest')->get();

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'registration',
            'action' => 'edit',
            'registration' => $registration,
            'users' => $users,
        ])->render()]);
    }

    /**
     * Display the specified registration details (read-only modal)
     */
    public function show(Registration $registration)
    {
        $registration->loadMissing('user');

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'registration',
            'registration' => $registration,
        ])->render()]);
    }

    /**
     * Update the specified registration in storage
     */
    public function update(Request $request, Registration $registration, PaymentService $paymentService)
    {
        // Status-only edit (to prevent changing core registration data)
        $validated = $request->validate([
            'status' => 'required|in:pending,approved_awaiting_payment,pending_due,active,rejected',
            'reject_reason' => 'nullable|string|max:255',
        ]);

        $currentStatus = $registration->status ?? 'pending';
        $nextStatus = $validated['status'];

        $pendingLikeStatuses = ['pending', 'approved_awaiting_payment', 'pending_due'];

        // No changes requested
        if ($currentStatus === $nextStatus) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada perubahan status.',
            ]);
        }

        // Scenario B:
        // - pending-like → approved_awaiting_payment (approve, generate registration fee bill)
        // - approved_awaiting_payment/pending_due → active only if registration fee is paid
        // - pending-like → rejected
        if (in_array($currentStatus, $pendingLikeStatuses, true)) {
            if ($nextStatus === 'approved_awaiting_payment') {
                $autoBillingResult = DB::transaction(function () use ($registration, $paymentService) {
                    // Approve (scenario B): mark as awaiting payment + set deadline.
                    $deadline = now()->addDays(7)->toDateString();
                    $grace = now()->addDays(10)->toDateString();

                    $registration->status = 'approved_awaiting_payment';
                    $registration->reject_reason = null;
                    $registration->payment_deadline = $registration->payment_deadline ?: $deadline;
                    $registration->grace_period_until = $registration->grace_period_until ?: $grace;
                    $registration->paid_late = false;
                    $registration->save();

                    // Ensure student record exists (based on registration JSON)
                    $candidate = $registration->candidate_data ?? [];
                    if (is_string($candidate)) {
                        $decodedCandidate = json_decode($candidate, true);
                        $candidate = is_array($decodedCandidate) ? $decodedCandidate : [];
                    }

                    $student = Student::query()
                        ->where('id_registration', $registration->id_registration)
                        ->first();

                    if (!$student) {
                        $student = Student::create([
                            'id_parents' => null,
                            'id_registration' => $registration->id_registration,
                            'name' => $candidate['name'] ?? 'Calon Murid',
                            'birth_place' => $candidate['birth_place'] ?? null,
                            'birth_date' => $candidate['birth_date'] ?? null,
                            'gender' => $candidate['gender'] ?? null,
                            'group' => $registration->group ?? null,
                            'status' => 'pending_payment',
                        ]);
                    } else {
                        if (!$student->id_registration) {
                            $student->id_registration = $registration->id_registration;
                        }
                        $student->group = $student->group ?: ($registration->group ?? null);
                        if (in_array(($student->status ?? null), ['aktif', 'rejected'], true)) {
                            // keep status if already active/rejected
                        } else {
                            $student->status = 'pending_payment';
                        }
                        $student->save();
                    }

                    // Semi-automatic billing: create ONLY the registration fee bill.
                    // Convention: master payment is identified by jenis_payment === 'uang_pendaftaran'.
                    $master = Payment::query()
                        ->where('jenis_payment', 'uang_pendaftaran')
                        ->where('is_active', true)
                        ->orderByDesc('id_payment')
                        ->first();

                    if (!$master) {
                        return 'skipped_missing_master';
                    }

                    // Safety guard: uang_pendaftaran should be one-time.
                    if (($master->period_mode ?? 'one_time') !== 'one_time') {
                        return 'skipped_wrong_mode';
                    }

                    $exists = StudentPayment::query()
                        ->where('id_student', (int)$student->id_student)
                        ->where('id_payment', (int)$master->id_payment)
                        ->where('payment_period', 'ONCE')
                        ->exists();

                    if ($exists) {
                        return 'skipped_exists';
                    }

                    $paymentService->createStudentPayment([
                        'payment' => $master,
                        'id_student' => (int)$student->id_student,
                        'payment_period' => 'ONCE',
                        'discount_amount' => 0,
                    ]);

                    return 'created';
                });

                $extra = match ($autoBillingResult) {
                    'created' => ' Tagihan uang pendaftaran otomatis dibuat.',
                    'skipped_exists' => ' Tagihan uang pendaftaran sudah ada.',
                    'skipped_wrong_mode' => ' Master payment uang pendaftaran bukan one_time; tagihan tidak dibuat.',
                    'skipped_missing_master' => ' Master payment uang pendaftaran belum ada; tagihan tidak dibuat.',
                    default => '',
                };

                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil di-approve (menunggu pembayaran).' . $extra,
                ]);
            }

            if ($nextStatus === 'active') {
                // Allow manual activation ONLY if registration fee bill is already paid.
                $student = Student::query()->where('id_registration', $registration->id_registration)->first();
                if (!$student) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data murid untuk pendaftaran ini belum ada.',
                    ], 422);
                }

                $master = Payment::query()
                    ->where('jenis_payment', 'uang_pendaftaran')
                    ->where('is_active', true)
                    ->orderByDesc('id_payment')
                    ->first();

                if (!$master) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Master payment uang pendaftaran belum ada. Tidak bisa mengaktifkan pendaftaran.',
                    ], 422);
                }

                $paid = StudentPayment::query()
                    ->where('id_student', (int)$student->id_student)
                    ->where('id_payment', (int)$master->id_payment)
                    ->where('payment_period', 'ONCE')
                    ->where('status', 'paid')
                    ->exists();

                if (!$paid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belum ada tagihan uang pendaftaran yang Paid. Aktivasi dilakukan otomatis setelah bukti pembayaran disetujui.',
                    ], 422);
                }

                $registration->status = 'active';
                $registration->reject_reason = null;
                $registration->payment_deadline = null;
                $registration->grace_period_until = null;
                $registration->paid_late = (bool)($registration->paid_late ?? false);
                $registration->save();

                if (($student->status ?? null) !== 'aktif') {
                    $student->status = 'aktif';
                    $student->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran diaktifkan.',
                ]);
            }

            if ($nextStatus === 'rejected') {
                $validatedReject = $request->validate([
                    'reject_reason' => 'required|string|max:255',
                ]);

                $registration->status = $nextStatus;
                $registration->reject_reason = $validatedReject['reject_reason'];
                $registration->payment_deadline = null;
                $registration->grace_period_until = null;
                $registration->paid_late = false;
                $registration->save();

                // Keep student in sync if exists.
                $student = Student::query()->where('id_registration', $registration->id_registration)->first();
                if ($student && ($student->status ?? null) !== 'rejected') {
                    $student->status = 'rejected';
                    $student->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Pendaftaran berhasil ditolak.',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Status pendaftaran hanya bisa diproses dari Pending/Pembayaran ke Menunggu Pembayaran, Aktif (jika sudah Paid), atau Tolak.',
        ], 422);
    }

    /**
     * Export registrations to CSV
     */
    public function export(Request $request)
    {
        $query = Registration::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('group', 'like', "%{$search}%")
                    ->orWhere('candidate_data->name', 'like', "%{$search}%")
                    ->orWhere('parents_data->father_name', 'like', "%{$search}%")
                    ->orWhere('parents_data->mother_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'pending') {
                $query->whereIn('status', ['pending', 'approved_awaiting_payment', 'pending_due']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('group') && $request->input('group') !== 'all') {
            $query->where('group', $request->input('group'));
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="registrations_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($registrations) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Nama Calon', 'Grup', 'Status', 'Deadline', 'Dibuat Tanggal']);

            foreach ($registrations as $reg) {
                $candidateName = $reg->candidate_data['name'] ?? '-';
                $deadline = $reg->payment_deadline ? $reg->payment_deadline->format('Y-m-d') : '-';

                fputcsv($file, [
                    $reg->id_registration,
                    $candidateName,
                    $reg->group ?? '-',
                    $reg->status,
                    $deadline,
                    $reg->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
