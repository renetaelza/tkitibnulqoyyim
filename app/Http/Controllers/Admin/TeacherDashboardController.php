<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\TeacherDetail;
use App\Models\TeacherHonor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = TeacherDetail::query()
            ->with(['classes:id_class,class_name'])
            ->withCount('classes')
            ->where('id_user', (int) ($user?->id ?? 0))
            ->first();

        $tz = config('attendance.timezone', 'Asia/Makassar');
        $nowTz = now($tz);
        $monthStart = $nowTz->copy()->startOfMonth();
        $monthEnd = $nowTz->copy()->endOfMonth();

        // Honor bulan ini (cari row teacher_honors yang periodenya cover bulan berjalan).
        $honorThisMonth = null;
        $attendanceCounts = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'late' => 0];
        $studentsCount = 0;
        $recentAttendance = collect();

        if ($teacher) {
            $honorThisMonth = TeacherHonor::query()
                ->where('id_teacher', $teacher->id_teacher)
                ->where('month', $nowTz->month)
                ->where('year', $nowTz->year)
                ->orderByDesc('created_at')
                ->first();

            // Counts absensi bulan ini.
            $rows = TeacherAttendance::query()
                ->selectRaw('status, COUNT(*) AS total, SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) AS late_total')
                ->where('id_teacher', $teacher->id_teacher)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->groupBy('status')
                ->get();

            foreach ($rows as $row) {
                $key = $row->status;
                if (isset($attendanceCounts[$key])) {
                    $attendanceCounts[$key] = (int) $row->total;
                }
            }
            $attendanceCounts['late'] = (int) TeacherAttendance::query()
                ->where('id_teacher', $teacher->id_teacher)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->where('is_late', true)
                ->count();

            // Total murid dari semua kelas teacher.
            $studentsCount = (int) \DB::table('class_student')
                ->whereIn('id_class', $teacher->classes->pluck('id_class'))
                ->distinct()
                ->count('id_student');

            // Riwayat 7 hari terakhir.
            $recentAttendance = TeacherAttendance::query()
                ->where('id_teacher', $teacher->id_teacher)
                ->where('date', '>=', $nowTz->copy()->subDays(6)->toDateString())
                ->orderByDesc('date')
                ->limit(7)
                ->get();
        }

        return view('dashboard.teacher.index', [
            'user' => $user,
            'teacher' => $teacher,
            'honorThisMonth' => $honorThisMonth,
            'attendanceCounts' => $attendanceCounts,
            'classesCount' => (int) ($teacher?->classes_count ?? 0),
            'studentsCount' => $studentsCount,
            'recentAttendance' => $recentAttendance,
            'tz' => $tz,
            'now' => $nowTz,
        ]);
    }
}
