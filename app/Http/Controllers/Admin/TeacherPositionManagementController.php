<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\TeacherDetail;
use App\Models\TeacherPosition;
use Illuminate\Http\Request;

class TeacherPositionManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherPosition::query()->with(['teacher', 'position']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher', function ($qt) use ($search) {
                    $qt->where('name', 'like', "%{$search}%");
                })->orWhereHas('position', function ($qp) use ($search) {
                    $qp->where('name', 'like', "%{$search}%");
                });
            });
        }

        $query->orderByDesc('effective_from')->orderByDesc('created_at');

        $perPage = $request->input('per_page', 10);
        $assignments = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.teacher-positions', [
            'assignments' => $assignments,
            'search' => $request->input('search', ''),
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        $teachers = TeacherDetail::query()
            ->orderBy('name', 'asc')
            ->get(['id_teacher', 'name', 'status']);

        $positions = Position::query()
            ->orderBy('name', 'asc')
            ->get(['id_position', 'name', 'is_active']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher-position',
            'action' => 'create',
            'teacherPosition' => null,
            'teachers' => $teachers,
            'positions' => $positions,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_teacher' => ['required', 'integer', 'exists:teacher_details,id_teacher'],
            'id_position' => ['required', 'integer', 'exists:positions,id_position'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        TeacherPosition::create($validated);

        return response()->json(['success' => true, 'message' => 'Posisi guru berhasil ditambahkan']);
    }

    public function edit(TeacherPosition $teacherPosition)
    {
        $teacherPosition->loadMissing(['teacher', 'position']);

        $teachers = TeacherDetail::query()
            ->orderBy('name', 'asc')
            ->get(['id_teacher', 'name', 'status']);

        $positions = Position::query()
            ->orderBy('name', 'asc')
            ->get(['id_position', 'name', 'is_active']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher-position',
            'action' => 'edit',
            'teacherPosition' => $teacherPosition,
            'teachers' => $teachers,
            'positions' => $positions,
        ])->render()]);
    }

    public function show(TeacherPosition $teacherPosition)
    {
        $teacherPosition->loadMissing(['teacher', 'position']);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'teacher-position',
            'teacherPosition' => $teacherPosition,
        ])->render()]);
    }

    public function update(Request $request, TeacherPosition $teacherPosition)
    {
        $validated = $request->validate([
            'id_teacher' => ['required', 'integer', 'exists:teacher_details,id_teacher'],
            'id_position' => ['required', 'integer', 'exists:positions,id_position'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        $teacherPosition->update($validated);

        return response()->json(['success' => true, 'message' => 'Posisi guru berhasil diperbarui']);
    }

    public function destroy(TeacherPosition $teacherPosition)
    {
        $teacherPosition->delete();

        return response()->json(['success' => true, 'message' => 'Posisi guru berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = TeacherPosition::query()->with(['teacher', 'position']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher', function ($qt) use ($search) {
                    $qt->where('name', 'like', "%{$search}%");
                })->orWhereHas('position', function ($qp) use ($search) {
                    $qp->where('name', 'like', "%{$search}%");
                });
            });
        }

        $rows = $query->orderByDesc('effective_from')->orderByDesc('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="teacher_positions_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Guru',
                'Posisi',
                'Periode Mulai',
                'Periode Akhir',
                'Dibuat Tanggal',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->id_teacher_position,
                    $row->teacher?->name ?? '-',
                    $row->position?->name ?? '-',
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
