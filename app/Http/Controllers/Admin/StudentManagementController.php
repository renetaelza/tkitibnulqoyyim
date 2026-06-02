<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentManagementController extends Controller
{
    /**
     * Display a listing of students with search and filter
     */
    public function index(Request $request)
    {
        $query = Student::with(['parent', 'registration']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('birth_place', 'like', "%{$search}%")
                    ->orWhere('group', 'like', "%{$search}%")
                    ->orWhere('religion', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter by group
        if ($request->filled('group') && $request->input('group') !== 'all') {
            $query->where('group', $request->input('group'));
        }

        // Filter by gender
        if ($request->filled('gender') && $request->input('gender') !== 'all') {
            $query->where('gender', $request->input('gender'));
        }

        // Sort
        $sortBy = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $students = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.students', [
            'students' => $students,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
            'group' => $request->input('group', 'all'),
            'gender' => $request->input('gender', 'all'),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        $parents = ParentGuardian::orderBy('id_parents', 'desc')->get();
        $registrations = Registration::orderBy('id_registration', 'desc')->get();

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'student',
            'action' => 'create',
            'student' => null,
            'parents' => $parents,
            'registrations' => $registrations,
        ])->render()]);
    }

    /**
     * Store a newly created student in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_parents' => 'nullable|exists:parents,id_parents',
            'id_registration' => 'nullable|exists:registrations,id_registration',
            'name' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:pria,perempuan',
            'religion' => 'nullable|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'group' => 'nullable|string|max:50',
            'status' => 'nullable|in:pending_payment,aktif,non-aktif,lulus,pindah,rejected',
        ]);

        $validated['status'] = $validated['status'] ?? 'pending_payment';

        Student::create($validated);

        return response()->json(['success' => true, 'message' => 'Data murid berhasil ditambahkan']);
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(Student $student)
    {
        $parents = ParentGuardian::orderBy('id_parents', 'desc')->get();
        $registrations = Registration::orderBy('id_registration', 'desc')->get();

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'student',
            'action' => 'edit',
            'student' => $student,
            'parents' => $parents,
            'registrations' => $registrations,
        ])->render()]);
    }

    /**
     * Display the specified student details (read-only modal)
     */
    public function show(Student $student)
    {
        $student->loadMissing(['parent.user', 'registration']);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'student',
            'student' => $student,
        ])->render()]);
    }

    /**
     * Update the specified student in storage
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'id_parents' => 'nullable|exists:parents,id_parents',
            'id_registration' => 'nullable|exists:registrations,id_registration',
            'name' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:pria,perempuan',
            'religion' => 'nullable|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'group' => 'nullable|string|max:50',
            'status' => 'nullable|in:pending_payment,aktif,non-aktif,lulus,pindah,rejected',
        ]);

        $validated['status'] = $validated['status'] ?? ($student->status ?? 'pending_payment');

        $student->update($validated);

        return response()->json(['success' => true, 'message' => 'Data murid berhasil diperbarui']);
    }

    /**
     * Export students to CSV
     */
    public function export(Request $request)
    {
        $query = Student::with(['parent', 'registration']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('birth_place', 'like', "%{$search}%")
                    ->orWhere('group', 'like', "%{$search}%")
                    ->orWhere('religion', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('group') && $request->input('group') !== 'all') {
            $query->where('group', $request->input('group'));
        }

        if ($request->filled('gender') && $request->input('gender') !== 'all') {
            $query->where('gender', $request->input('gender'));
        }

        $students = $query->orderBy('name', 'asc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="students_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Nama', 'Agama', 'Gender', 'Grup', 'Status', 'Dibuat Tanggal']);

            foreach ($students as $s) {
                fputcsv($file, [
                    $s->id_student,
                    $s->name,
                    $s->religion ?? '-',
                    $s->gender ?? '-',
                    $s->group ?? '-',
                    $s->status,
                    $s->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
