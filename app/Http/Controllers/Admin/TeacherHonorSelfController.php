<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherDetail;
use App\Models\TeacherHonor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherHonorSelfController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $teacher = TeacherDetail::query()
            ->where('id_user', (int)($user?->id ?? 0))
            ->first();

        $perPage = (int)$request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $honors = null;
        $myHonor = null;
        $myHonorLatest = null;
        $myHonorList = collect();

        if ($teacher) {
            $honors = TeacherHonor::query()
                ->where('id_teacher', (int)$teacher->id_teacher)
                ->orderByDesc('period_start')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->orderByDesc('created_at')
                ->paginate($perPage)
                ->appends($request->query());

            $now = now();

            $myHonor = TeacherHonor::query()
                ->where('id_teacher', (int)$teacher->id_teacher)
                ->whereNotNull('period_start')
                ->whereNotNull('period_end')
                ->whereDate('period_start', '<=', $now)
                ->whereDate('period_end', '>=', $now)
                ->orderByDesc('period_start')
                ->orderByDesc('created_at')
                ->first();

            if (!$myHonor) {
                $myHonor = TeacherHonor::query()
                    ->where('id_teacher', (int)$teacher->id_teacher)
                    ->where('month', (int)$now->month)
                    ->where('year', (int)$now->year)
                    ->orderByDesc('created_at')
                    ->first();
            }

            $myHonorLatest = TeacherHonor::query()
                ->where('id_teacher', (int)$teacher->id_teacher)
                ->orderByDesc('period_start')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->orderByDesc('created_at')
                ->first();

            $myHonorList = TeacherHonor::query()
                ->where('id_teacher', (int)$teacher->id_teacher)
                ->orderByDesc('period_start')
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->orderByDesc('created_at')
                ->take(6)
                ->get();
        }

        return view('dashboard.admin.my-honor', [
            'user' => $user,
            'teacher' => $teacher,
            'honors' => $honors,
            'per_page' => $perPage,
            'myTeacherDetail' => $teacher,
            'myHonor' => $myHonor,
            'myHonorLatest' => $myHonorLatest,
            'myHonorList' => $myHonorList,
        ]);
    }
}
