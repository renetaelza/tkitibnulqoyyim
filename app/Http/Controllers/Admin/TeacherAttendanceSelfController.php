<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\TeacherDetail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherAttendanceSelfController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->resolveTeacher();

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $tz = config('attendance.timezone', 'Asia/Makassar');
        $nowTz = now($tz);
        $today = $nowTz->toDateString();

        $todayAttendance = null;
        $attendances = null;

        if ($teacher) {
            $todayAttendance = TeacherAttendance::query()
                ->where('id_teacher', (int) $teacher->id_teacher)
                ->whereDate('date', $today)
                ->first();

            $attendances = TeacherAttendance::query()
                ->where('id_teacher', (int) $teacher->id_teacher)
                ->orderByDesc('date')
                ->orderByDesc('id_attendance')
                ->paginate($perPage)
                ->appends($request->query());
        }

        $window = $this->windowState($nowTz);

        return view('dashboard.admin.my-attendance', [
            'teacher' => $teacher,
            'todayAttendance' => $todayAttendance,
            'attendances' => $attendances,
            'per_page' => $perPage,
            'now' => $nowTz,
            'window' => $window,
            'policy' => [
                'check_in_open' => config('attendance.check_in_open'),
                'late_after' => config('attendance.late_after'),
                'check_in_close' => config('attendance.check_in_close'),
                'timezone' => $tz,
            ],
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $teacher = $this->resolveTeacher();
        $this->ensureTeacher($teacher);

        $tz = config('attendance.timezone', 'Asia/Makassar');
        $nowTz = now($tz);
        $today = $nowTz->toDateString();

        $existing = TeacherAttendance::query()
            ->where('id_teacher', (int) $teacher->id_teacher)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'check_in' => ['Anda sudah melakukan absensi hari ini.'],
            ]);
        }

        $window = $this->windowState($nowTz);
        if (!$window['can_check_in']) {
            throw ValidationException::withMessages([
                'check_in' => [$window['reason'] ?? 'Di luar jam absen yang diizinkan.'],
            ]);
        }

        $lateAfter = $this->timeOfDay($nowTz, config('attendance.late_after'));
        $isLate = $nowTz->greaterThanOrEqualTo($lateAfter);
        $lateMinutes = $isLate ? (int) $lateAfter->diffInMinutes($nowTz) : 0;

        TeacherAttendance::create([
            'id_teacher' => (int) $teacher->id_teacher,
            'date' => $today,
            'check_in_time' => $nowTz->copy()->utc(),
            'is_late' => $isLate,
            'late_minutes' => $isLate ? $lateMinutes : null,
            'status' => 'hadir',
            'information' => null,
            'attachment_path' => null,
            'source' => 'self',
        ]);

        return redirect()
            ->route('admin.my-attendance.index')
            ->with('success', $isLate
                ? "Absensi tersimpan. Anda tercatat terlambat {$lateMinutes} menit."
                : 'Absensi tersimpan tepat waktu.');
    }

    public function permission(Request $request): RedirectResponse
    {
        return $this->submitNonAttendance($request, 'izin', true);
    }

    public function sick(Request $request): RedirectResponse
    {
        return $this->submitNonAttendance($request, 'sakit', false);
    }

    private function submitNonAttendance(Request $request, string $status, bool $attachmentRequired): RedirectResponse
    {
        $teacher = $this->resolveTeacher();
        $this->ensureTeacher($teacher);

        $rules = [
            'date' => ['required', 'date'],
            'information' => ['nullable', 'string', 'max:1000'],
        ];

        $mimes = config('attendance.attachment_mimes', 'jpg,jpeg,png,pdf');
        $maxKb = (int) config('attendance.attachment_max_kb', 2048);

        $attachmentRule = ['file', "mimes:{$mimes}", "max:{$maxKb}"];
        if ($attachmentRequired) {
            array_unshift($attachmentRule, 'required');
        } else {
            array_unshift($attachmentRule, 'nullable');
        }
        $rules['attachment'] = $attachmentRule;

        $validated = $request->validate($rules);

        $date = Carbon::parse($validated['date'])->toDateString();

        $existing = TeacherAttendance::query()
            ->where('id_teacher', (int) $teacher->id_teacher)
            ->whereDate('date', $date)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'date' => ['Anda sudah memiliki entri absensi untuk tanggal tersebut.'],
            ]);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $disk = config('attendance.attachment_disk', 'public');
            $dir = config('attendance.attachment_dir', 'teacher-attendance');
            $stored = $request->file('attachment')->store($dir, $disk);
            $attachmentPath = 'storage/' . $stored;
        }

        TeacherAttendance::create([
            'id_teacher' => (int) $teacher->id_teacher,
            'date' => $date,
            'check_in_time' => null,
            'is_late' => false,
            'late_minutes' => null,
            'status' => $status,
            'information' => $validated['information'] ?? null,
            'attachment_path' => $attachmentPath,
            'source' => 'self',
        ]);

        $label = $status === 'izin' ? 'Izin' : 'Sakit';

        return redirect()
            ->route('admin.my-attendance.index')
            ->with('success', "{$label} berhasil dicatat untuk tanggal {$date}.");
    }

    private function resolveTeacher(): ?TeacherDetail
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return TeacherDetail::query()
            ->where('id_user', (int) $user->id)
            ->first();
    }

    private function ensureTeacher(?TeacherDetail $teacher): void
    {
        if (!$teacher) {
            throw ValidationException::withMessages([
                'teacher' => ['Akun Anda belum tertaut ke data guru. Hubungi admin.'],
            ]);
        }
    }

    /**
     * @return array{can_check_in:bool, reason:?string, opens_at:Carbon, closes_at:Carbon, late_at:Carbon}
     */
    private function windowState(Carbon $nowTz): array
    {
        $open = $this->timeOfDay($nowTz, config('attendance.check_in_open'));
        $late = $this->timeOfDay($nowTz, config('attendance.late_after'));
        $close = $this->timeOfDay($nowTz, config('attendance.check_in_close'));

        $isWorkday = in_array($nowTz->dayOfWeekIso, (array) config('attendance.workdays', [1,2,3,4,5]), true);

        $reason = null;
        $canCheckIn = true;

        if (!$isWorkday) {
            $canCheckIn = false;
            $reason = 'Hari ini bukan hari kerja (Senin–Jumat).';
        } elseif ($nowTz->lessThan($open)) {
            $canCheckIn = false;
            $reason = 'Absen dibuka pukul ' . $open->format('H:i') . ' WITA.';
        } elseif ($nowTz->greaterThan($close)) {
            $canCheckIn = false;
            $reason = 'Absen sudah ditutup pukul ' . $close->format('H:i') . ' WITA.';
        }

        return [
            'can_check_in' => $canCheckIn,
            'reason' => $reason,
            'opens_at' => $open,
            'closes_at' => $close,
            'late_at' => $late,
            'is_workday' => $isWorkday,
        ];
    }

    private function timeOfDay(Carbon $nowTz, ?string $hhmm): Carbon
    {
        [$h, $m] = array_pad(explode(':', (string) ($hhmm ?? '00:00')), 2, '0');
        return $nowTz->copy()->setTime((int) $h, (int) $m, 0);
    }
}
