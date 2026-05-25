<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::query();

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

        $query->orderByDesc('created_at')->orderByDesc('id_position');

        $perPage = $request->input('per_page', 10);
        $positions = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.positions', [
            'positions' => $positions,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'position',
            'action' => 'create',
            'position' => null,
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

        Position::create($validated);

        return response()->json(['success' => true, 'message' => 'Posisi berhasil ditambahkan']);
    }

    public function edit(Position $position)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'position',
            'action' => 'edit',
            'position' => $position,
        ])->render()]);
    }

    public function show(Position $position)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'position',
            'position' => $position,
        ])->render()]);
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool)($validated['is_active'] ?? true);

        $position->update($validated);

        return response()->json(['success' => true, 'message' => 'Posisi berhasil diperbarui']);
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return response()->json(['success' => true, 'message' => 'Posisi berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = Position::query();

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

        $positions = $query->orderByDesc('created_at')->orderByDesc('id_position')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="positions_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($positions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Nama',
                'Deskripsi',
                'Status',
                'Dibuat Tanggal',
            ]);

            foreach ($positions as $p) {
                fputcsv($file, [
                    $p->id_position,
                    $p->name ?? '-',
                    $p->description ?? '-',
                    ($p->is_active ?? false) ? 'Aktif' : 'Nonaktif',
                    $p->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
