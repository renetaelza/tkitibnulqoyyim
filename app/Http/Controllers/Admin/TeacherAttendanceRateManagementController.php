<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendanceRate;
use App\Models\TeacherDetail;
use Illuminate\Http\Request;

class TeacherAttendanceRateManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherAttendanceRate::query()->with(['teacher']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher', function ($qt) use ($search) {
                    $qt->where('name', 'like', "%{$search}%");
                });
            });
        }

        $query->orderByDesc('effective_from')->orderByDesc('created_at');

        $perPage = $request->input('per_page', 10);
        $rates = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.teacher-attendance-rates', [
            'rates' => $rates,
            'search' => $request->input('search', ''),
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        $teachers = TeacherDetail::query()
            ->orderBy('name', 'asc')
            ->get(['id_teacher', 'name', 'status']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher-attendance-rate',
            'action' => 'create',
            'teacherAttendanceRate' => null,
            'teachers' => $teachers,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_teacher' => ['required', 'integer', 'exists:teacher_details,id_teacher'],
            'amount_per_attendance' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        TeacherAttendanceRate::create($validated);

        return response()->json(['success' => true, 'message' => 'Tarif kehadiran berhasil ditambahkan']);
    }

    public function edit(TeacherAttendanceRate $teacherAttendanceRate)
    {
        $teacherAttendanceRate->loadMissing(['teacher']);

        $teachers = TeacherDetail::query()
            ->orderBy('name', 'asc')
            ->get(['id_teacher', 'name', 'status']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher-attendance-rate',
            'action' => 'edit',
            'teacherAttendanceRate' => $teacherAttendanceRate,
            'teachers' => $teachers,
        ])->render()]);
    }

    public function show(TeacherAttendanceRate $teacherAttendanceRate)
    {
        $teacherAttendanceRate->loadMissing(['teacher']);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'teacher-attendance-rate',
            'teacherAttendanceRate' => $teacherAttendanceRate,
        ])->render()]);
    }

    public function update(Request $request, TeacherAttendanceRate $teacherAttendanceRate)
    {
        $validated = $request->validate([
            'id_teacher' => ['required', 'integer', 'exists:teacher_details,id_teacher'],
            'amount_per_attendance' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        $teacherAttendanceRate->update($validated);

        return response()->json(['success' => true, 'message' => 'Tarif kehadiran berhasil diperbarui']);
    }

    public function destroy(TeacherAttendanceRate $teacherAttendanceRate)
    {
        $teacherAttendanceRate->delete();

        return response()->json(['success' => true, 'message' => 'Tarif kehadiran berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = TeacherAttendanceRate::query()->with(['teacher']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher', function ($qt) use ($search) {
                    $qt->where('name', 'like', "%{$search}%");
                });
            });
        }

        $rows = $query->orderByDesc('effective_from')->orderByDesc('created_at')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="teacher_attendance_rates_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Guru',
                'Tarif Per Hadir',
                'Periode Mulai',
                'Periode Akhir',
                'Dibuat Tanggal',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->id_teacher_attendance_rate,
                    $row->teacher?->name ?? '-',
                    (float) ($row->amount_per_attendance ?? 0),
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
