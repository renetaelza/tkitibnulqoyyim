<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowanceType;
use Illuminate\Http\Request;

class AllowanceTypeManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = AllowanceType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status') === 'active';
            $query->where('is_active', $status);
        }

        $query->orderByDesc('created_at')->orderByDesc('id_allowance_type');

        $perPage = $request->input('per_page', 10);
        $types = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.allowance-types', [
            'types' => $types,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'allowance-type',
            'action' => 'create',
            'allowanceType' => null,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool)($validated['is_active'] ?? true);

        AllowanceType::create($validated);

        return response()->json(['success' => true, 'message' => 'Jenis tunjangan berhasil ditambahkan']);
    }

    public function edit(AllowanceType $allowanceType)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'allowance-type',
            'action' => 'edit',
            'allowanceType' => $allowanceType,
        ])->render()]);
    }

    public function show(AllowanceType $allowanceType)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'allowance-type',
            'allowanceType' => $allowanceType,
        ])->render()]);
    }

    public function update(Request $request, AllowanceType $allowanceType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool)($validated['is_active'] ?? true);

        $allowanceType->update($validated);

        return response()->json(['success' => true, 'message' => 'Jenis tunjangan berhasil diperbarui']);
    }

    public function destroy(AllowanceType $allowanceType)
    {
        $allowanceType->delete();

        return response()->json(['success' => true, 'message' => 'Jenis tunjangan berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = AllowanceType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status') === 'active';
            $query->where('is_active', $status);
        }

        $types = $query->orderByDesc('created_at')->orderByDesc('id_allowance_type')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="allowance_types_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($types) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Nama',
                'Deskripsi',
                'Status',
                'Dibuat Tanggal',
            ]);

            foreach ($types as $t) {
                fputcsv($file, [
                    $t->id_allowance_type,
                    $t->name ?? '-',
                    $t->description ?? '-',
                    ($t->is_active ?? false) ? 'Aktif' : 'Nonaktif',
                    $t->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
