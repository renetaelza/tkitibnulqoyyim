<?php

namespace Database\Seeders;

use App\Models\TeacherAttendanceRate;
use App\Models\TeacherDetail;
use Illuminate\Database\Seeder;

class TeacherAttendanceRateSeeder extends Seeder
{
    /**
     * Seed tarif per kehadiran untuk setiap guru.
     * firstOrCreate agar tidak menimpa tarif yang sudah diubah admin.
     */
    public function run(): void
    {
        // email => amount_per_attendance
        $rates = [
            'headmaster@example.com' => 100_000,
            'teacher@example.com'    => 75_000,
            'teacher2@example.com'   => 75_000,
            'teacher3@example.com'   => 65_000,
        ];

        foreach ($rates as $email => $amount) {
            $teacher = TeacherDetail::query()
                ->whereHas('user', fn($q) => $q->where('email', $email))
                ->first();

            if (! $teacher) {
                continue;
            }

            TeacherAttendanceRate::query()->firstOrCreate(
                [
                    'id_teacher'   => $teacher->id_teacher,
                    'effective_to' => null,
                ],
                [
                    'amount_per_attendance' => $amount,
                    'effective_from'        => '2024-07-01',
                ]
            );
        }
    }
}
