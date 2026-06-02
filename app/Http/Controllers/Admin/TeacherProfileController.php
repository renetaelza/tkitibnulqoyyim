<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherDetail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();
        $teacher = TeacherDetail::query()
            ->where('id_user', (int) ($user?->id ?? 0))
            ->first();

        return view('dashboard.teacher.profile', [
            'user' => $user,
            'teacher' => $teacher,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            throw ValidationException::withMessages(['auth' => ['Tidak terautentikasi.']]);
        }

        $teacher = TeacherDetail::query()->where('id_user', (int) $user->id)->first();
        if (!$teacher) {
            throw ValidationException::withMessages([
                'teacher' => ['Akun Anda belum tertaut ke data guru. Hubungi admin.'],
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:120'],
            'nip' => ['nullable', 'string', 'max:50'],
            'nuptk' => ['nullable', 'string', 'max:50'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'start_work_date' => ['nullable', 'date'],
            'education' => ['required', 'string', 'max:255'],
            'phone_num' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            // optional password change
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $teacher->update([
            'name' => $validated['name'],
            'position' => $validated['position'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'birth_place' => $validated['birth_place'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'start_work_date' => $validated['start_work_date'] ?? null,
            'education' => $validated['education'],
            'phone_num' => $validated['phone_num'],
            'email' => $validated['email'] ?? null,
        ]);

        // Ubah password (opsional)
        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'] ?? '', $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Password lama salah.'],
                ]);
            }
            User::query()->where('id', $user->id)->update([
                'password' => Hash::make($validated['new_password']),
            ]);
        }

        return redirect()
            ->route('admin.teacher.profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
