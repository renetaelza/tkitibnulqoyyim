<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacilityManagementController extends Controller
{
    private function deleteStoredImageIfLocal(?string $imagePath): void
    {
        $raw = trim((string)($imagePath ?? ''));
        if ($raw === '') {
            return;
        }

        // Only delete local storage files (we keep external URLs untouched).
        if (preg_match('~^https?://~i', $raw)) {
            return;
        }

        $normalized = ltrim(str_replace('\\', '/', $raw), '/');
        if (!str_starts_with($normalized, 'storage/')) {
            return;
        }

        $relative = substr($normalized, strlen('storage/'));
        if ($relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }

    /**
     * Display a listing of facilities with search and filters.
     */
    public function index(Request $request)
    {
        $query = Facility::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('condition', 'like', "%{$search}%")
                    ->orWhere('fund_source', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('image_path', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            }
            if ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $query->orderByDesc('created_at')->orderByDesc('id');

        $perPage = $request->input('per_page', 10);
        $facilities = $query->paginate($perPage)->appends($request->query());

        return view('dashboard.admin.facilities', [
            'facilities' => $facilities,
            'search' => $request->input('search', ''),
            'status' => $request->input('status', 'all'),
            'per_page' => $perPage,
        ]);
    }

    public function create()
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'facility',
            'action' => 'create',
            'facility' => null,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['nullable', 'string', 'max:255'],
            'fund_source' => ['nullable', 'string', 'max:120'],
            'acquisition_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'category' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['quantity'] = (int)($validated['quantity'] ?? 0);
        $validated['is_active'] = (bool)($validated['is_active'] ?? true);

        if ($request->hasFile('image')) {
            $stored = $request->file('image')->store('facilities', 'public');
            $validated['image_path'] = 'storage/' . $stored;
        }

        Facility::create($validated);

        return response()->json(['success' => true, 'message' => 'Fasilitas berhasil ditambahkan']);
    }

    public function edit(Facility $facility)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'facility',
            'action' => 'edit',
            'facility' => $facility,
        ])->render()]);
    }

    public function show(Facility $facility)
    {
        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'facility',
            'facility' => $facility,
        ])->render()]);
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['nullable', 'string', 'max:255'],
            'fund_source' => ['nullable', 'string', 'max:120'],
            'acquisition_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'category' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['quantity'] = (int)($validated['quantity'] ?? ($facility->quantity ?? 0));
        $validated['is_active'] = (bool)($validated['is_active'] ?? true);

        if ($request->hasFile('image')) {
            $this->deleteStoredImageIfLocal($facility->image_path);
            $stored = $request->file('image')->store('facilities', 'public');
            $validated['image_path'] = 'storage/' . $stored;
        }

        $facility->update($validated);

        return response()->json(['success' => true, 'message' => 'Fasilitas berhasil diperbarui']);
    }

    public function destroy(Facility $facility)
    {
        $this->deleteStoredImageIfLocal($facility->image_path);
        $facility->delete();

        return response()->json(['success' => true, 'message' => 'Fasilitas berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = Facility::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('condition', 'like', "%{$search}%")
                    ->orWhere('fund_source', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('image_path', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            }
            if ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $facilities = $query->orderByDesc('created_at')->orderByDesc('id')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="facilities_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $callback = function () use ($facilities) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
                'Nama',
                'Deskripsi',
                'Jumlah',
                'Kondisi',
                'Sumber Dana',
                'Tahun',
                'Kategori',
                'Path Gambar',
                'Aktif',
                'Dibuat Tanggal',
            ]);

            foreach ($facilities as $f) {
                fputcsv($file, [
                    $f->id,
                    $f->name ?? '-',
                    $f->description ?? '-',
                    (int)($f->quantity ?? 0),
                    $f->condition ?? '-',
                    $f->fund_source ?? '-',
                    $f->acquisition_year ?? '-',
                    $f->category ?? '-',
                    $f->image_path ?? '-',
                    ($f->is_active ?? false) ? 'Aktif' : 'Nonaktif',
                    $f->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
