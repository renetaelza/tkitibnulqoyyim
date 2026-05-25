<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowanceType;
use App\Models\Position;
use App\Models\PositionAllowance;
use Illuminate\Http\Request;

class PositionAllowanceManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = PositionAllowance::query()->with(['position', 'allowanceType']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('position', function ($qp) use ($search) {
                    $qp->where('name', 'like', "%{$search}%");
                })->orWhereHas('allowanceType', function ($qa) use ($search) {
                    $qa->where('name', 'like', "%{$search}%");
                });
            });
        }

        $query->orderByDesc('effective_from')->orderByDesc('created_at');

        $perPage = $request->input('per_page', 10);
        $allowances = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.position-allowances', [
            'allowances' => $allowances,
            'search' => $request->input('search', ''),
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        $positions = Position::query()->orderBy('name', 'asc')->get(['id_position', 'name']);
        $types = AllowanceType::query()->orderBy('name', 'asc')->get(['id_allowance_type', 'name']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'position-allowance',
            'action' => 'create',
            'positionAllowance' => null,
            'positions' => $positions,
            'allowanceTypes' => $types,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_position' => ['required', 'integer', 'exists:positions,id_position'],
            'id_allowance_type' => ['required', 'integer', 'exists:allowance_types,id_allowance_type'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        PositionAllowance::create($validated);

        return response()->json(['success' => true, 'message' => 'Tunjangan posisi berhasil ditambahkan']);
    }

    public function edit(PositionAllowance $positionAllowance)
    {
        $positionAllowance->loadMissing(['position', 'allowanceType']);

        $positions = Position::query()->orderBy('name', 'asc')->get(['id_position', 'name']);
        $types = AllowanceType::query()->orderBy('name', 'asc')->get(['id_allowance_type', 'name']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'position-allowance',
            'action' => 'edit',
            'positionAllowance' => $positionAllowance,
            'positions' => $positions,
            'allowanceTypes' => $types,
        ])->render()]);
    }

    public function show(PositionAllowance $positionAllowance)
    {
        $positionAllowance->loadMissing(['position', 'allowanceType']);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'position-allowance',
            'positionAllowance' => $positionAllowance,
        ])->render()]);
    }

    public function update(Request $request, PositionAllowance $positionAllowance)
    {
        $validated = $request->validate([
            'id_position' => ['required', 'integer', 'exists:positions,id_position'],
            'id_allowance_type' => ['required', 'integer', 'exists:allowance_types,id_allowance_type'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        $positionAllowance->update($validated);

        return response()->json(['success' => true, 'message' => 'Tunjangan posisi berhasil diperbarui']);
    }

    public function destroy(PositionAllowance $positionAllowance)
    {
        $positionAllowance->delete();

        return response()->json(['success' => true, 'message' => 'Tunjangan posisi berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = PositionAllowance::query()->with(['position', 'allowanceType']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('position', function ($qp) use ($search) {
                    $qp->where('name', 'like', "%{$search}%");
                })->orWhereHas('allowanceType', function ($qa) use ($search) {
                    $qa->where('name', 'like', "%{$search}%");
                });
            });
        }

        $rows = $query->orderByDesc('effective_from')->orderByDesc('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="position_allowances_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Posisi',
                'Jenis Tunjangan',
                'Nominal',
                'Periode Mulai',
                'Periode Akhir',
                'Dibuat Tanggal',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->id_position_allowance,
                    $row->position?->name ?? '-',
                    $row->allowanceType?->name ?? '-',
                    (float) ($row->amount ?? 0),
                    $row->effective_from?->format('Y-m-d') ?? '-',
                    $row->effective_to?->format('Y-m-d') ?? '-',
                    $row->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
