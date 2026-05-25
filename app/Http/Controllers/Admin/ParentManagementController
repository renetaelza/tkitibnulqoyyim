<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\User;
use Illuminate\Http\Request;

class ParentManagementController extends Controller
{
    /**
     * Display a listing of parents/guardians with search
     */
    public function index(Request $request)
    {
        $query = ParentGuardian::with([
            'user',
            'students:id_student,id_parents,name',
        ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('father_name', 'like', "%{$search}%")
                    ->orWhere('mother_name', 'like', "%{$search}%")
                    ->orWhere('father_phone_num', 'like', "%{$search}%")
                    ->orWhere('mother_phone_num', 'like', "%{$search}%")
                    ->orWhere('father_occupation', 'like', "%{$search}%")
                    ->orWhere('mother_occupation', 'like', "%{$search}%");
            });
        }

        // Helpful filter: contact availability
        if ($request->filled('contact') && $request->input('contact') !== 'all') {
            $contact = $request->input('contact');
            if ($contact === 'has_contact') {
                $query->where(function ($q) {
                    $q->whereNotNull('father_phone_num')->where('father_phone_num', '!=', '')
                        ->orWhereNotNull('mother_phone_num')->where('mother_phone_num', '!=', '');
                });
            }

            if ($contact === 'no_contact') {
                $query->where(function ($q) {
                    $q->whereNull('father_phone_num')->orWhere('father_phone_num', '=', '');
                })->where(function ($q) {
                    $q->whereNull('mother_phone_num')->orWhere('mother_phone_num', '=', '');
                });
            }
        }

        $sortBy = $request->input('sort', 'id_parents');
        $sortOrder = $request->input('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $parents = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.parents', [
            'parents' => $parents,
            'search' => $request->input('search', ''),
            'contact' => $request->input('contact', 'all'),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new parent record
     */
    public function create()
    {
        $users = User::where('role', 'guest')->whereDoesntHave('parentGuardian')->get();

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'parent',
            'action' => 'create',
            'parent' => null,
            'users' => $users,
        ])->render()]);
    }

    /**
     * Store a newly created parent record in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id|unique:parents,id_user',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'father_phone_num' => 'nullable|string|max:50',
            'mother_phone_num' => 'nullable|string|max:50',
            'father_occupation' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'father_address' => 'nullable|string|max:2000',
            'mother_address' => 'nullable|string|max:2000',
        ]);

        ParentGuardian::create($validated);

        return response()->json(['success' => true, 'message' => 'Data orang tua berhasil ditambahkan']);
    }

    /**
     * Show the form for editing the specified parent record
     */
    public function edit(ParentGuardian $parent)
    {
        $users = User::where('role', 'guest')->get();

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'parent',
            'action' => 'edit',
            'parent' => $parent,
            'users' => $users,
        ])->render()]);
    }

    /**
     * Display the specified parent details (read-only modal)
     */
    public function show(ParentGuardian $parent)
    {
        $parent->loadMissing('user');

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'parent',
            'parent' => $parent,
        ])->render()]);
    }

    /**
     * Update the specified parent record in storage
     */
    public function update(Request $request, ParentGuardian $parent)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id|unique:parents,id_user,' . $parent->id_parents . ',id_parents',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'father_phone_num' => 'nullable|string|max:50',
            'mother_phone_num' => 'nullable|string|max:50',
            'father_occupation' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'father_address' => 'nullable|string|max:2000',
            'mother_address' => 'nullable|string|max:2000',
        ]);

        $parent->update($validated);

        return response()->json(['success' => true, 'message' => 'Data orang tua berhasil diperbarui']);
    }

    /**
     * Export parents to CSV
     */
    public function export(Request $request)
    {
        $query = ParentGuardian::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('father_name', 'like', "%{$search}%")
                    ->orWhere('mother_name', 'like', "%{$search}%")
                    ->orWhere('father_phone_num', 'like', "%{$search}%")
                    ->orWhere('mother_phone_num', 'like', "%{$search}%")
                    ->orWhere('father_occupation', 'like', "%{$search}%")
                    ->orWhere('mother_occupation', 'like', "%{$search}%");
            });
        }

        if ($request->filled('contact') && $request->input('contact') !== 'all') {
            $contact = $request->input('contact');
            if ($contact === 'has_contact') {
                $query->where(function ($q) {
                    $q->whereNotNull('father_phone_num')->where('father_phone_num', '!=', '')
                        ->orWhereNotNull('mother_phone_num')->where('mother_phone_num', '!=', '');
                });
            }

            if ($contact === 'no_contact') {
                $query->where(function ($q) {
                    $q->whereNull('father_phone_num')->orWhere('father_phone_num', '=', '');
                })->where(function ($q) {
                    $q->whereNull('mother_phone_num')->orWhere('mother_phone_num', '=', '');
                });
            }
        }

        $parents = $query->orderBy('id_parents', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="parents_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($parents) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Nama Ayah', 'Nama Ibu', 'HP Ayah', 'HP Ibu', 'Dibuat Tanggal']);

            foreach ($parents as $p) {
                fputcsv($file, [
                    $p->id_parents,
                    $p->father_name ?? '-',
                    $p->mother_name ?? '-',
                    $p->father_phone_num ?? '-',
                    $p->mother_phone_num ?? '-',
                    $p->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
