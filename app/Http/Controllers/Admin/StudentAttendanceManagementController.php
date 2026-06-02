<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeacherDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAttendanceManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = StudentAttendance::query()->with([
            'student',
            'student.classes' => fn ($q) => $q
                ->select('classes.id_class', 'class_name', 'school_year')
                ->orderBy('class_student.created_at', 'desc'),
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('id_class') && $request->input('id_class') !== 'all') {
            $idClass = $request->input('id_class');
            $query->whereHas('student.classes', fn ($q) => $q->where('classes.id_class', $idClass));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        $query->orderBy('date', 'desc')->orderBy('id_attendance', 'desc');

        $perPage = $request->input('per_page', 10);
        $attendances = $query->paginate($perPage)->appends($request->query());

        $classes = SchoolClass::query()
            ->orderBy('class_name', 'asc')
            ->get(['id_class', 'class_name', 'school_year']);

        return view('dashboard.admin.student-attendance', [
            'attendances' => $attendances,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
            'id_class' => $request->input('id_class', 'all'),
            'classes' => $classes,
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        $students = Student::query()->orderBy('name', 'asc')->get(['id_student', 'name']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'student-attendance',
            'action' => 'create',
            'studentAttendance' => null,
            'students' => $students,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_student' => 'required|array|min:1',
            'id_student.*' => 'integer|exists:students,id_student',
            'date' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'information' => 'nullable|string',
        ]);

        $studentIds = array_values(array_unique($validated['id_student']));

        // Security: kalau yang submit adalah teacher, pastikan id_student[] semua
        // berada di kelas yang dia ajar. Admin/headmaster boleh untuk semua siswa.
        $user = Auth::user();
        if (($user?->role ?? null) === 'teacher') {
            $teacher = TeacherDetail::query()->where('id_user', (int) $user->id)->first();
            if (!$teacher) {
                throw ValidationException::withMessages([
                    'id_student' => ['Akun Anda belum tertaut ke data guru.'],
                ]);
            }

            $teacherClassIds = DB::table('class_teacher')
                ->where('id_teacher', $teacher->id_teacher)
                ->pluck('id_class')
                ->all();

            $allowedStudentIds = DB::table('class_student')
                ->whereIn('id_class', $teacherClassIds)
                ->pluck('id_student')
                ->all();

            $allowedLookup = array_flip($allowedStudentIds);
            $disallowed = array_filter($studentIds, fn ($id) => !isset($allowedLookup[$id]));

            if (!empty($disallowed)) {
                throw ValidationException::withMessages([
                    'id_student' => ['Anda hanya boleh mencatat absensi murid di kelas yang Anda ajar.'],
                ]);
            }
        }

        $existingIds = StudentAttendance::query()
            ->whereIn('id_student', $studentIds)
            ->whereDate('date', $validated['date'])
            ->pluck('id_student')
            ->all();

        $existingLookup = array_flip($existingIds);
        $toCreate = array_values(array_filter($studentIds, fn ($id) => !isset($existingLookup[$id])));

        if (empty($toCreate)) {
            throw ValidationException::withMessages([
                'id_student' => ['Semua murid yang dipilih sudah memiliki absensi pada tanggal tersebut.'],
            ]);
        }

        foreach ($toCreate as $studentId) {
            StudentAttendance::create([
                'id_student' => $studentId,
                'date' => $validated['date'],
                'status' => $validated['status'],
                'information' => $validated['information'] ?? null,
            ]);
        }

        $createdCount = count($toCreate);
        $duplicateCount = count($studentIds) - $createdCount;

        $message = $createdCount > 1
            ? "Absensi murid berhasil ditambahkan ({$createdCount} data)."
            : 'Absensi murid berhasil ditambahkan.';

        if ($duplicateCount > 0) {
            $message .= " {$duplicateCount} murid dilewati karena sudah ada absensi di tanggal tersebut.";
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function show(StudentAttendance $studentAttendance)
    {
        $studentAttendance->loadMissing(['student']);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'student-attendance',
            'studentAttendance' => $studentAttendance,
            'student' => $studentAttendance->student,
        ])->render()]);
    }

    public function edit(StudentAttendance $studentAttendance)
    {
        $students = Student::query()->orderBy('name', 'asc')->get(['id_student', 'name']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'student-attendance',
            'action' => 'edit',
            'studentAttendance' => $studentAttendance,
            'students' => $students,
        ])->render()]);
    }

    public function update(Request $request, StudentAttendance $studentAttendance)
    {
        $validated = $request->validate([
            'id_student' => 'required|exists:students,id_student',
            'date' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'information' => 'nullable|string',
        ]);

        $exists = StudentAttendance::query()
            ->where('id_student', $validated['id_student'])
            ->whereDate('date', $validated['date'])
            ->where('id_attendance', '!=', $studentAttendance->id_attendance)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'date' => ['Absensi murid untuk tanggal tersebut sudah ada.'],
            ]);
        }

        $studentAttendance->update($validated);

        return response()->json(['success' => true, 'message' => 'Absensi murid berhasil diperbarui']);
    }

    public function destroy(StudentAttendance $studentAttendance)
    {
        $studentAttendance->delete();

        return response()->json(['success' => true, 'message' => 'Absensi murid berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = StudentAttendance::query()->with([
            'student',
            'student.classes' => fn ($q) => $q
                ->select('classes.id_class', 'class_name', 'school_year')
                ->orderBy('class_student.created_at', 'desc'),
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('id_class') && $request->input('id_class') !== 'all') {
            $idClass = $request->input('id_class');
            $query->whereHas('student.classes', fn ($q) => $q->where('classes.id_class', $idClass));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        $attendances = $query->orderBy('date', 'desc')->orderBy('id_attendance', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="student_attendance_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Tanggal', 'ID Murid', 'Nama Murid', 'Status', 'Keterangan', 'Dibuat Tanggal']);

            foreach ($attendances as $a) {
                fputcsv($file, [
                    $a->id_attendance,
                    $a->date?->format('Y-m-d') ?? '-',
                    $a->id_student,
                    $a->student?->name ?? '-',
                    $a->status,
                    $a->information ?? '-',
                    $a->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
