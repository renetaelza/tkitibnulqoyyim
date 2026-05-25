<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherDetail;
use Illuminate\Http\Request;

class SchoolClassManagementController extends Controller
{
    /**
     * Display a listing of classes with search and filter
     */
    public function index(Request $request)
    {
        $query = SchoolClass::query()->withCount(['students']);

        // Filter: school year
        if ($request->filled('school_year') && $request->input('school_year') !== 'all') {
            $query->where('school_year', $request->input('school_year'));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'like', "%{$search}%")
                    ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->input('sort', 'class_name');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $classes = $query->paginate($perPage)->appends($request->query());

        $schoolYears = SchoolClass::query()
            ->whereNotNull('school_year')
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year');

        return view('dashboard.admin.classes', [
            'classes' => $classes,
            'search' => $request->input('search', ''),
            'per_page' => $perPage,
            'school_year' => $request->input('school_year', 'all'),
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Show the form for creating a new class
     */
    public function create()
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'class',
            'action' => 'create',
            'schoolClass' => null,
        ])->render()]);
    }

    /**
     * Store a newly created class in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'max_students' => 'nullable|integer|min:1|max:1000',
        ]);

        SchoolClass::create($validated);

        return response()->json(['success' => true, 'message' => 'Data kelas berhasil ditambahkan']);
    }

    /**
     * Show the form for editing the specified class
     */
    public function edit(SchoolClass $schoolClass)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'class',
            'action' => 'edit',
            'schoolClass' => $schoolClass,
        ])->render()]);
    }

    /**
     * Display the specified class details (read-only modal)
     */
    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->load([
            'students' => fn ($q) => $q->orderBy('name', 'asc'),
            'teachers' => fn ($q) => $q->orderBy('name', 'asc'),
        ])->loadCount(['students', 'teachers']);

        $assignedStudentIds = $schoolClass->students->pluck('id_student')->filter()->values();
        $assignedTeacherIds = $schoolClass->teachers->pluck('id_teacher')->filter()->values();

        $availableStudents = Student::query()
            ->orderBy('name', 'asc')
            ->when($assignedStudentIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id_student', $assignedStudentIds))
            ->get(['id_student', 'name', 'group', 'status']);

        $availableTeachers = TeacherDetail::query()
            ->orderBy('name', 'asc')
            ->when($assignedTeacherIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id_teacher', $assignedTeacherIds))
            ->get(['id_teacher', 'name', 'status']);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'class',
            'schoolClass' => $schoolClass,
            'classStudents' => $schoolClass->students,
            'classTeachers' => $schoolClass->teachers,
            'availableStudents' => $availableStudents,
            'availableTeachers' => $availableTeachers,
        ])->render()]);
    }

    /**
     * Attach a student to a class (class_student)
     */
    public function attachStudent(Request $request, SchoolClass $schoolClass)
    {
        $request->merge([
            'id_student' => array_values(array_filter((array) $request->input('id_student'))),
        ]);

        $validated = $request->validate([
            'id_student' => 'required|array|min:1',
            'id_student.*' => 'integer|exists:students,id_student',
        ]);

        $studentIds = collect($validated['id_student'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $schoolClass->students()->syncWithoutDetaching($studentIds);

        $count = count($studentIds);
        $message = $count === 1
            ? 'Murid berhasil ditambahkan ke kelas'
            : $count . ' murid berhasil ditambahkan ke kelas';

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Detach a student from a class (class_student)
     */
    public function detachStudent(SchoolClass $schoolClass, Student $student)
    {
        $schoolClass->students()->detach($student->id_student);

        return response()->json(['success' => true, 'message' => 'Murid berhasil dihapus dari kelas']);
    }

    /**
     * Attach a teacher to a class (class_teacher)
     */
    public function attachTeacher(Request $request, SchoolClass $schoolClass)
    {
        $request->merge([
            'id_teacher' => array_values(array_filter((array) $request->input('id_teacher'))),
        ]);

        $validated = $request->validate([
            'id_teacher' => 'required|array|min:1',
            'id_teacher.*' => 'integer|exists:teacher_details,id_teacher',
        ]);

        $teacherIds = collect($validated['id_teacher'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $schoolClass->teachers()->syncWithoutDetaching($teacherIds);

        $count = count($teacherIds);
        $message = $count === 1
            ? 'Guru berhasil ditambahkan ke kelas'
            : $count . ' guru berhasil ditambahkan ke kelas';

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Detach a teacher from a class (class_teacher)
     */
    public function detachTeacher(SchoolClass $schoolClass, TeacherDetail $teacher)
    {
        $schoolClass->teachers()->detach($teacher->id_teacher);

        return response()->json(['success' => true, 'message' => 'Guru berhasil dihapus dari kelas']);
    }

    /**
     * Update the specified class in storage
     */
    public function update(Request $request, SchoolClass $schoolClass)
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'max_students' => 'nullable|integer|min:1|max:1000',
        ]);

        $schoolClass->update($validated);

        return response()->json(['success' => true, 'message' => 'Data kelas berhasil diperbarui']);
    }

    /**
     * Export classes to CSV
     */
    public function export(Request $request)
    {
        $query = SchoolClass::query();

        if ($request->filled('school_year') && $request->input('school_year') !== 'all') {
            $query->where('school_year', $request->input('school_year'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('class_name', 'like', "%{$search}%")
                    ->orWhere('school_year', 'like', "%{$search}%");
            });
        }

        $classes = $query->orderBy('class_name', 'asc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="classes_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($classes) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Nama Kelas', 'Tahun Ajaran', 'Dibuat Tanggal']);

            foreach ($classes as $c) {
                fputcsv($file, [
                    $c->id_class,
                    $c->class_name,
                    $c->school_year,
                    $c->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
