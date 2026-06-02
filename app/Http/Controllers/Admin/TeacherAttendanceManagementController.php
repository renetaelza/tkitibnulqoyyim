<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\TeacherDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TeacherAttendanceManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherAttendance::query()->with(['teacher']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('teacher', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_teacher') && $request->input('id_teacher') !== 'all') {
            $query->where('id_teacher', $request->input('id_teacher'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
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

        $teachers = TeacherDetail::query()->orderBy('name', 'asc')->get(['id_teacher', 'name']);

        return view('dashboard.admin.teacher-attendance', [
            'attendances' => $attendances,
            'teachers'    => $teachers,
            'search'      => $request->input('search', ''),
            'id_teacher'  => $request->input('id_teacher', 'all'),
            'status'      => $request->input('status', 'all'),
            'date_from'   => $request->input('date_from', ''),
            'date_to'     => $request->input('date_to', ''),
            'per_page'    => $perPage,
        ]);
    }

    public function create()
    {
        $teachers = TeacherDetail::query()->orderBy('name', 'asc')->get(['id_teacher', 'name']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher-attendance',
            'action' => 'create',
            'teacherAttendance' => null,
            'teachers' => $teachers,
        ])->render()]);
    }

    public function store(Request $request)
    {
        $mimes = config('attendance.attachment_mimes', 'jpg,jpeg,png,pdf');
        $maxKb = (int) config('attendance.attachment_max_kb', 2048);

        $validated = $request->validate([
            'id_teacher' => 'required|array|min:1',
            'id_teacher.*' => 'integer|exists:teacher_details,id_teacher',
            'date' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'information' => 'nullable|string',
            'attachment' => ['nullable', 'file', "mimes:{$mimes}", "max:{$maxKb}"],
        ]);

        if ($validated['status'] === 'izin' && !$request->hasFile('attachment')) {
            throw ValidationException::withMessages([
                'attachment' => ['Bukti wajib diunggah untuk status Izin.'],
            ]);
        }

        $teacherIds = array_values(array_unique($validated['id_teacher']));

        $existingIds = TeacherAttendance::query()
            ->whereIn('id_teacher', $teacherIds)
            ->whereDate('date', $validated['date'])
            ->pluck('id_teacher')
            ->all();

        $existingLookup = array_flip($existingIds);
        $toCreate = array_values(array_filter($teacherIds, fn ($id) => !isset($existingLookup[$id])));

        if (empty($toCreate)) {
            throw ValidationException::withMessages([
                'id_teacher' => ['Semua guru yang dipilih sudah memiliki absensi pada tanggal tersebut.'],
            ]);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $disk = config('attendance.attachment_disk', 'public');
            $dir = config('attendance.attachment_dir', 'teacher-attendance');
            $stored = $request->file('attachment')->store($dir, $disk);
            $attachmentPath = 'storage/' . $stored;
        }

        foreach ($toCreate as $teacherId) {
            TeacherAttendance::create([
                'id_teacher' => $teacherId,
                'date' => $validated['date'],
                'status' => $validated['status'],
                'information' => $validated['information'] ?? null,
                'attachment_path' => $attachmentPath,
                'source' => 'admin',
            ]);
        }

        $createdCount = count($toCreate);
        $duplicateCount = count($teacherIds) - $createdCount;

        $message = $createdCount > 1
            ? "Absensi guru berhasil ditambahkan ({$createdCount} data)."
            : 'Absensi guru berhasil ditambahkan.';

        if ($duplicateCount > 0) {
            $message .= " {$duplicateCount} guru dilewati karena sudah ada absensi di tanggal tersebut.";
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function show(TeacherAttendance $teacherAttendance)
    {
        $teacherAttendance->loadMissing([
            'teacher.user',
            'teacher.classes' => fn ($q) => $q->orderBy('class_name', 'asc'),
        ]);

        return response()->json(['view' => view('components.dashboard.admin.modal-detail', [
            'type' => 'teacher-attendance',
            'teacherAttendance' => $teacherAttendance,
            'teacher' => $teacherAttendance->teacher,
        ])->render()]);
    }

    public function edit(TeacherAttendance $teacherAttendance)
    {
        $teachers = TeacherDetail::query()->orderBy('name', 'asc')->get(['id_teacher', 'name']);

        return response()->json(['view' => view('components.dashboard.admin.modal-form', [
            'type' => 'teacher-attendance',
            'action' => 'edit',
            'teacherAttendance' => $teacherAttendance,
            'teachers' => $teachers,
        ])->render()]);
    }

    public function update(Request $request, TeacherAttendance $teacherAttendance)
    {
        $mimes = config('attendance.attachment_mimes', 'jpg,jpeg,png,pdf');
        $maxKb = (int) config('attendance.attachment_max_kb', 2048);

        $validated = $request->validate([
            'id_teacher' => 'required|exists:teacher_details,id_teacher',
            'date' => 'required|date',
            'status' => 'required|in:hadir,izin,sakit,alpa',
            'information' => 'nullable|string',
            'check_in_time' => 'nullable|date',
            'attachment' => ['nullable', 'file', "mimes:{$mimes}", "max:{$maxKb}"],
        ]);

        $exists = TeacherAttendance::query()
            ->where('id_teacher', $validated['id_teacher'])
            ->whereDate('date', $validated['date'])
            ->where('id_attendance', '!=', $teacherAttendance->id_attendance)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'date' => ['Absensi guru untuk tanggal tersebut sudah ada.'],
            ]);
        }

        // Bukti wajib untuk izin, kecuali sudah ada attachment_path sebelumnya.
        if ($validated['status'] === 'izin'
            && !$request->hasFile('attachment')
            && empty($teacherAttendance->attachment_path)) {
            throw ValidationException::withMessages([
                'attachment' => ['Bukti wajib diunggah untuk status Izin.'],
            ]);
        }

        $payload = [
            'id_teacher' => $validated['id_teacher'],
            'date' => $validated['date'],
            'status' => $validated['status'],
            'information' => $validated['information'] ?? null,
        ];

        // Hitung is_late dari check_in_time (jika diisi).
        if (!empty($validated['check_in_time'])) {
            $tz = config('attendance.timezone', 'Asia/Makassar');
            $checkIn = Carbon::parse($validated['check_in_time'], $tz);
            $lateAfter = $this->timeOfDayFor($checkIn, config('attendance.late_after'));
            $isLate = $checkIn->greaterThanOrEqualTo($lateAfter);
            $lateMinutes = $isLate ? (int) $lateAfter->diffInMinutes($checkIn) : 0;

            $payload['check_in_time'] = $checkIn->copy()->utc();
            $payload['is_late'] = $isLate;
            $payload['late_minutes'] = $isLate ? $lateMinutes : null;
        } else {
            $payload['check_in_time'] = null;
            $payload['is_late'] = false;
            $payload['late_minutes'] = null;
        }

        if ($request->hasFile('attachment')) {
            $this->deleteStoredAttachmentIfLocal($teacherAttendance->attachment_path);
            $disk = config('attendance.attachment_disk', 'public');
            $dir = config('attendance.attachment_dir', 'teacher-attendance');
            $stored = $request->file('attachment')->store($dir, $disk);
            $payload['attachment_path'] = 'storage/' . $stored;
        }

        $teacherAttendance->update($payload);

        return response()->json(['success' => true, 'message' => 'Absensi guru berhasil diperbarui']);
    }

    private function timeOfDayFor(Carbon $reference, ?string $hhmm): Carbon
    {
        [$h, $m] = array_pad(explode(':', (string) ($hhmm ?? '00:00')), 2, '0');
        return $reference->copy()->setTime((int) $h, (int) $m, 0);
    }

    private function deleteStoredAttachmentIfLocal(?string $path): void
    {
        $raw = trim((string) ($path ?? ''));
        if ($raw === '' || preg_match('~^https?://~i', $raw)) {
            return;
        }
        $normalized = ltrim(str_replace('\\', '/', $raw), '/');
        if (!str_starts_with($normalized, 'storage/')) {
            return;
        }
        $relative = substr($normalized, strlen('storage/'));
        if ($relative !== '') {
            Storage::disk(config('attendance.attachment_disk', 'public'))->delete($relative);
        }
    }

    public function destroy(TeacherAttendance $teacherAttendance)
    {
        $teacherAttendance->delete();

        return response()->json(['success' => true, 'message' => 'Absensi guru berhasil dihapus']);
    }

    public function export(Request $request)
    {
        $query = TeacherAttendance::query()->with(['teacher']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('teacher', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('id_teacher') && $request->input('id_teacher') !== 'all') {
            $query->where('id_teacher', $request->input('id_teacher'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
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
            'Content-Disposition' => 'attachment; filename="teacher_attendance_' . now()->format('Y-m-d_H-i-s') . '.csv"',
        ];

        $tz = config('attendance.timezone', 'Asia/Makassar');

        $callback = function () use ($attendances, $tz) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Tanggal', 'ID Guru', 'Nama Guru', 'Status',
                'Check-in (WITA)', 'Telat', 'Menit Telat',
                'Keterangan', 'Bukti', 'Sumber', 'Dibuat Tanggal',
            ]);

            foreach ($attendances as $a) {
                $checkInTz = $a->check_in_time?->copy()->setTimezone($tz);

                fputcsv($file, [
                    $a->id_attendance,
                    $a->date?->format('Y-m-d') ?? '-',
                    $a->id_teacher,
                    $a->teacher?->name ?? '-',
                    $a->status,
                    $checkInTz?->format('Y-m-d H:i') ?? '-',
                    $a->is_late ? 'Ya' : 'Tidak',
                    $a->late_minutes ?? '-',
                    $a->information ?? '-',
                    $a->attachment_path ?? '-',
                    $a->source ?? '-',
                    $a->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
