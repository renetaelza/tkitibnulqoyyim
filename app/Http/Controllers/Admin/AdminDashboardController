<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherHonor;
use App\Models\User;
use App\Models\TeacherDetail;
use App\Models\Student;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin dashboard with statistics
     */
    public function index()
    {
        $user = Auth::user();
        $stats = $this->getStats();

        $myHonor = null;
        $myHonorLatest = null;
        $myHonorList = collect();
        $myTeacherDetail = null;

        $roleRaw = (string)($user?->role ?? '');
        $role = $roleRaw === 'super_admin' ? 'superadmin' : $roleRaw;

        if ($user && $role === 'teacher') {
            $myTeacherDetail = TeacherDetail::query()
                ->where('id_user', (int)$user->id)
                ->first();

            if ($myTeacherDetail) {
                $now = now();

                $myHonor = TeacherHonor::query()
                    ->where('id_teacher', (int)$myTeacherDetail->id_teacher)
                    ->whereNotNull('period_start')
                    ->whereNotNull('period_end')
                    ->whereDate('period_start', '<=', $now)
                    ->whereDate('period_end', '>=', $now)
                    ->orderByDesc('period_start')
                    ->orderByDesc('created_at')
                    ->first();

                if (!$myHonor) {
                    $myHonor = TeacherHonor::query()
                        ->where('id_teacher', (int)$myTeacherDetail->id_teacher)
                        ->where('month', (int)$now->month)
                        ->where('year', (int)$now->year)
                        ->orderByDesc('created_at')
                        ->first();
                }

                $myHonorLatest = TeacherHonor::query()
                    ->where('id_teacher', (int)$myTeacherDetail->id_teacher)
                    ->orderByDesc('period_start')
                    ->orderByDesc('year')
                    ->orderByDesc('month')
                    ->orderByDesc('created_at')
                    ->first();

                $myHonorList = TeacherHonor::query()
                    ->where('id_teacher', (int)$myTeacherDetail->id_teacher)
                    ->orderByDesc('period_start')
                    ->orderByDesc('year')
                    ->orderByDesc('month')
                    ->orderByDesc('created_at')
                    ->take(6)
                    ->get();
            }
        }

        return view('dashboard.admin.index', [
            'user' => $user,
            'stats' => $stats,
            'myTeacherDetail' => $myTeacherDetail,
            'myHonor' => $myHonor,
            'myHonorLatest' => $myHonorLatest,
            'myHonorList' => $myHonorList,
        ]);
    }

    /**
     * Get statistics for dashboard overview
     */
    private function getStats(): array
    {
        $totalRevenue = 0.0;
        $outstandingPayments = 0.0;

        return [
            'total_users' => User::count(),
            'total_teachers' => TeacherDetail::count(),
            'total_students' => Student::count(),
            'total_classes' => \App\Models\SchoolClass::count(),
            'pending_registrations' => Registration::whereIn('status', ['pending', 'approved_awaiting_payment', 'pending_due'])->count(),
            'approved_registrations' => Registration::where('status', 'active')->count(),
            'total_revenue' => $totalRevenue,
            'outstanding_payments' => $outstandingPayments,
            'recent_registrations' => Registration::latest()->take(5)->get(),
            'active_users' => User::where('role', '!=', 'guest')->count(),
        ];
    }
}
