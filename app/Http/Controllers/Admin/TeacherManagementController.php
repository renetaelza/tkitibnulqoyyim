<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TeacherManagementController extends Controller
{
    /**
     * Display a listing of teachers with search and filter
     */
    public function index(Request $request)
    {
        $query = TeacherDetail::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_num', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('education', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_num', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if (Schema::hasColumn('teacher_details', 'status') && $request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Sort
        $sortBy = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $teachers = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.teachers', [
            'teachers' => $teachers,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new teacher
     */
    public function create()
    {
        $users = User::where('role', 'teacher')->whereDoesntHave('teacherDetail')->get();
        
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher',
            'action' => 'create',
            'teacher' => null,
            'users' => $users,
        ])->render()]);
    }

    /**
     * Store a newly created teacher in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id|unique:teacher_details,id_user',
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:120',
            'nip' => 'nullable|string|max:50',
            'nuptk' => 'nullable|string|max:50',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'start_work_date' => 'nullable|date',
            'education' => 'required|string|max:255',
            'phone_num' => 'required|string|max:20',
            'email' => 'nullable|email',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        TeacherDetail::create($validated);

        return response()->json(['success' => true, 'message' => 'Guru berhasil ditambahkan']);
    }

    /**
     * Show the form for editing the specified teacher
     */
    public function edit(TeacherDetail $teacher)
    {
        $users = User::where('role', 'teacher')->get();
        
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher',
            'action' => 'edit',
            'teacher' => $teacher,
            'users' => $users,
        ])->render()]);
    }

    /**
     * Display the specified teacher details (read-only modal)
     */
    public function show(TeacherDetail $teacher)
    {
        $teacher->loadMissing('user');

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'teacher',
            'teacher' => $teacher,
        ])->render()]);
    }

    /**
     * Update the specified teacher in storage
     */
    public function update(Request $request, TeacherDetail $teacher)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id|unique:teacher_details,id_user,' . $teacher->id_teacher . ',id_teacher',
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:120',
            'nip' => 'nullable|string|max:50',
            'nuptk' => 'nullable|string|max:50',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'start_work_date' => 'nullable|date',
            'education' => 'required|string|max:255',
            'phone_num' => 'required|string|max:20',
            'email' => 'nullable|email',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? ($teacher->status ?? 'active');

        $teacher->update($validated);

        return response()->json(['success' => true, 'message' => 'Guru berhasil diperbarui']);
    }

    /**
     * Delete the specified teacher
     */
    public function destroy(TeacherDetail $teacher)
    {
        $teacher->delete();

        return response()->json(['success' => true, 'message' => 'Guru berhasil dihapus']);
    }

    /**
     * Export teachers to CSV
     */
    public function export(Request $request)
    {
        $query = TeacherDetail::with('user');

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_num', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('education', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('nuptk', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_num', 'like', "%{$search}%");
                    });
            });
        }

        if (Schema::hasColumn('teacher_details', 'status') && $request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $teachers = $query->get();

        // Return as CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="teachers_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function() use ($teachers) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['ID Guru', 'ID User', 'Nama', 'Jabatan', 'NIP', 'NUPTK', 'TTL', 'Tgl Mulai Kerja', 'Masa Kerja', 'Email', 'Phone', 'Pendidikan', 'Status', 'Dibuat', 'Diperbarui']);
            
            // Data rows
            foreach ($teachers as $teacher) {
                $statusValue = $teacher->status ?? 'active';
                $statusLabel = $statusValue === 'active' ? 'Aktif' : 'Nonaktif';

                $emailValue = $teacher->email ?? ($teacher->user?->email ?? '-');
                $phoneValue = $teacher->phone_num ?? ($teacher->user?->phone_num ?? '-');
                $ttlLabel = ($teacher->birth_place || $teacher->birth_date)
                    ? trim(($teacher->birth_place ?? '-') . ', ' . ($teacher->birth_date?->format('Y-m-d') ?? '-'))
                    : '-';

                fputcsv($file, [
                    $teacher->id_teacher,
                    $teacher->id_user,
                    $teacher->name,
                    $teacher->position ?? '-',
                    $teacher->nip ?? '-',
                    $teacher->nuptk ?? '-',
                    $ttlLabel,
                    $teacher->start_work_date?->format('Y-m-d') ?? '-',
                    $teacher->masa_kerja,
                    $emailValue,
                    $phoneValue,
                    $teacher->education ?? '-',
                    $statusLabel,
                    $teacher->created_at?->format('Y-m-d H:i:s') ?? '-',
                    $teacher->updated_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
