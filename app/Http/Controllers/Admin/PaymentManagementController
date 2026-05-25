<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('jenis_payment', 'like', "%{$search}%");
            });
        }

        if ($request->filled('period_mode') && $request->input('period_mode') !== 'all') {
            $query->where('period_mode', $request->input('period_mode'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $sortBy = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int)$request->input('per_page', 10);
        $payments = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.payments', [
            'payments' => $payments,
            'search' => $request->input('search', ''),
            'period_mode' => $request->input('period_mode', 'all'),
            'status' => $request->input('status', 'all'),
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'payment',
            'action' => 'create',
            'payment' => null,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'jenis_payment' => ['required', 'string', 'max:255'],
            'period_mode' => ['required', 'in:one_time,monthly,school_year'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'detail_fee_json' => ['nullable', 'string'],
        ]);

        $detail = $this->decodeDetailFeeJson($request->input('detail_fee_json'));

        Payment::create([
            'name' => $validated['name'],
            'jenis_payment' => $validated['jenis_payment'],
            'period_mode' => $validated['period_mode'],
            'detail_fee_template' => $detail,
            'default_amount' => (float)($validated['default_amount'] ?? 0),
            'is_active' => (bool)($validated['is_active'] ?? true),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Payment master berhasil ditambahkan']);
    }

    public function edit(Payment $payment)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'payment',
            'action' => 'edit',
            'payment' => $payment,
        ])->render()]);
    }

    public function show(Payment $payment)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'payment',
            'payment' => $payment,
        ])->render()]);
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'jenis_payment' => ['required', 'string', 'max:255'],
            'period_mode' => ['required', 'in:one_time,monthly,school_year'],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'detail_fee_json' => ['nullable', 'string'],
        ]);

        $detail = $this->decodeDetailFeeJson($request->input('detail_fee_json'));

        $payment->update([
            'name' => $validated['name'],
            'jenis_payment' => $validated['jenis_payment'],
            'period_mode' => $validated['period_mode'],
            'detail_fee_template' => $detail,
            'default_amount' => (float)($validated['default_amount'] ?? 0),
            'is_active' => (bool)($validated['is_active'] ?? true),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Payment master berhasil diperbarui']);
    }

    public function destroy(Payment $payment)
    {
        if ($payment->studentPayments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment tidak bisa dihapus karena sudah memiliki tagihan murid.',
            ], 422);
        }

        $payment->delete();

        return response()->json(['success' => true, 'message' => 'Payment master berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = Payment::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('jenis_payment', 'like', "%{$search}%");
            });
        }

        if ($request->filled('period_mode') && $request->input('period_mode') !== 'all') {
            $query->where('period_mode', $request->input('period_mode'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $payments = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="payments_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($payments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nama', 'Jenis', 'Mode Periode', 'Default Amount', 'Aktif', 'Dibuat']);

            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->id_payment,
                    $p->name,
                    $p->jenis_payment,
                    $p->period_mode,
                    (string)($p->default_amount ?? 0),
                    ($p->is_active ? '1' : '0'),
                    $p->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function decodeDetailFeeJson(?string $json): ?array
    {
        $json = trim((string)($json ?? ''));
        if ($json === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw ValidationException::withMessages([
                'detail_fee_json' => 'Komponen biaya harus berupa JSON array yang valid.',
            ]);
        }

        // Keep it permissive but safe.
        $out = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) continue;
            $label = trim((string)($row['label'] ?? ''));
            $amount = (float)($row['amount'] ?? 0);
            $qty = (int)($row['qty'] ?? 1);
            if ($qty <= 0) $qty = 1;
            if ($label === '') $label = 'Komponen';

            $out[] = [
                'label' => $label,
                'amount' => $amount,
                'qty' => $qty,
            ];
        }

        return $out;
    }
}
