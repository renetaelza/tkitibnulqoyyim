<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\TeacherDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeacherStudentController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = TeacherDetail::query()
            ->with([
                'classes' => fn ($q) => $q->orderBy('class_name')->withCount('students'),
            ])
            ->where('id_user', (int) ($user?->id ?? 0))
            ->first();

        $classes = $teacher?->classes ?? collect();
        $activeClassId = (int) ($request->input('class') ?: ($classes->first()?->id_class ?? 0));

        $search = (string) $request->input('search', '');
        $students = collect();

        if ($activeClassId > 0) {
            $studentsQuery = SchoolClass::query()
                ->where('id_class', $activeClassId)
                ->whereIn('id_class', $classes->pluck('id_class'))
                ->with(['students' => fn ($q) => $q->with('parent:id_parents,father_name,mother_name')->orderBy('name')])
                ->first();

            if ($studentsQuery) {
                $students = $studentsQuery->students;
                if ($search !== '') {
                    $students = $students->filter(fn ($s) => stripos($s->name, $search) !== false)->values();
                }
            }
        }

        return view('dashboard.teacher.students', [
            'teacher' => $teacher,
            'classes' => $classes,
            'activeClassId' => $activeClassId,
            'students' => $students,
            'search' => $search,
        ]);
    }
}
