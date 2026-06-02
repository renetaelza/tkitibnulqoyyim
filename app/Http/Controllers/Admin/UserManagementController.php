<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users with search and filter
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_num', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        // Filter by status (if status column exists)
        if (Schema::hasColumn('users', 'status') && $request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Sort
        $sortBy = $request->input('sort', 'name');
        $sortOrder = $request->input('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.users', [
            'users' => $users,
            'search' => $request->input('search', ''),
            'role' => $request->input('role', 'all'),
            'status' => $request->input('status', 'all'),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'user',
            'action' => 'create',
            'user' => null,
        ])->render()]);
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_num' => 'required|string|max:20',
            'role' => 'required|in:superadmin,headmaster,administration,bendahara,teacher,guest',
            'status' => 'nullable|in:active,inactive',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        if (Schema::hasColumn('users', 'status')) {
            $validated['status'] = $request->input('status', 'active');
        } else {
            unset($validated['status']);
        }

        User::create($validated);

        return response()->json(['success' => true, 'message' => 'User berhasil ditambahkan']);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'user',
            'action' => 'edit',
            'user' => $user,
        ])->render()]);
    }

    /**
     * Display the specified user details (read-only modal)
     */
    public function show(User $user)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'user',
            'user' => $user,
        ])->render()]);
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone_num' => 'required|string|max:20',
            'role' => 'required|in:superadmin,headmaster,administration,bendahara,teacher,guest',
            'status' => 'nullable|in:active,inactive',
        ]);

        if (!Schema::hasColumn('users', 'status')) {
            unset($validated['status']);
        }

        // Only update password if provided
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $validated['password'] = bcrypt($request->input('password'));
        }

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'User berhasil diperbarui']);
    }

    /**
     * Delete the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting the logged-in user
        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus user yang sedang login'], 403);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User berhasil dihapus']);
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        $query = User::query()->with(['teacherDetail:id_teacher,id_user,name', 'parentGuardian:id_parents,id_user,father_name,mother_name']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_num', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        if (Schema::hasColumn('users', 'status') && $request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $users = $query->get();
        $hasStatus = Schema::hasColumn('users', 'status');

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="users_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $roleLabels = [
            'superadmin'     => 'Super Admin',
            'headmaster'     => 'Kepala Sekolah',
            'administration' => 'Administrasi',
            'bendahara'      => 'Bendahara',
            'teacher'        => 'Guru',
            'guest'          => 'Orang Tua',
        ];

        $callback = function () use ($users, $hasStatus, $roleLabels) {
            $file = fopen('php://output', 'w');

            $header = ['ID', 'Nama', 'Email', 'Telepon', 'Role', 'Dibuat Tanggal'];
            if ($hasStatus) {
                $header[] = 'Status';
            }
            fputcsv($file, $header);

            foreach ($users as $user) {
                // Display name: prefer teacher/parent detail name based on role.
                $displayName = $user->name;
                if ($user->role === 'teacher' && $user->teacherDetail?->name) {
                    $displayName = $user->teacherDetail->name;
                } elseif ($user->role === 'guest' && $user->parentGuardian) {
                    $father = trim((string) ($user->parentGuardian->father_name ?? ''));
                    $mother = trim((string) ($user->parentGuardian->mother_name ?? ''));
                    if ($father !== '' && $mother !== '') {
                        $displayName = $father . ' & ' . $mother;
                    } elseif ($father !== '') {
                        $displayName = $father;
                    } elseif ($mother !== '') {
                        $displayName = $mother;
                    }
                }

                $roleLabel = $roleLabels[$user->role] ?? ucfirst((string) $user->role);

                $statusLabel = '-';
                if ($hasStatus) {
                    $statusLabel = ($user->status ?? 'active') === 'active' ? 'Aktif' : 'Nonaktif';
                }

                $row = [
                    $user->id,
                    $displayName,
                    $user->email,
                    $user->phone_num ?? '-',
                    $roleLabel,
                    $user->created_at?->format('Y-m-d H:i:s') ?? '-',
                ];

                if ($hasStatus) {
                    $row[] = $statusLabel;
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
