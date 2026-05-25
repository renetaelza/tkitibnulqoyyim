<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TeacherDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeacherDetailSeeder extends Seeder
{
    public function run(): void
    {
        $teacherUser = User::query()->where('email', 'teacher@example.com')->first();
        if (! $teacherUser) {
            $teacherUser = User::query()->where('role', 'teacher')->first();
        }

        if (! $teacherUser) {
            return;
        }

        TeacherDetail::query()->updateOrCreate(
            ['id_user' => $teacherUser->id],
            [
                'name' => 'Ibu Samiyah, S.Pd',
                'education' => 'S1 Pendidikan',
                'phone_num' => '+62812345680',
                'email' => 'teacher@example.com',
                'status' => 'active',
            ]
        );
    }
}
