<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\TeacherDetail;
use App\Models\TeacherPosition;
use Illuminate\Database\Seeder;

class TeacherPositionSeeder extends Seeder
{
    public function run(): void
    {
        // Map teacher email → [position_name, effective_from]
        $assignments = [
            'headmaster@example.com' => [
                ['position' => 'Kepala Sekolah', 'effective_from' => '2024-07-01'],
            ],
            'teacher@example.com' => [
                ['position' => 'Wali Kelas A', 'effective_from' => '2024-07-01'],
            ],
            'teacher2@example.com' => [
                ['position' => 'Wali Kelas B', 'effective_from' => '2024-07-01'],
                ['position' => 'Bendahara',    'effective_from' => '2024-07-01'],
            ],
            'teacher3@example.com' => [
                ['position' => 'Guru Pendamping', 'effective_from' => '2024-07-01'],
            ],
        ];

        foreach ($assignments as $email => $roles) {
            $teacher = TeacherDetail::query()
                ->whereHas('user', fn($q) => $q->where('email', $email))
                ->first();

            if (! $teacher) {
                continue;
            }

            foreach ($roles as $role) {
                $position = Position::query()->where('name', $role['position'])->first();
                if (! $position) {
                    continue;
                }

                // One active assignment per teacher+position (no end date = still active)
                TeacherPosition::query()->firstOrCreate(
                    [
                        'id_teacher'   => $teacher->id_teacher,
                        'id_position'  => $position->id_position,
                        'effective_to' => null,
                    ],
                    [
                        'effective_from' => $role['effective_from'],
                    ]
                );
            }
        }
    }
}
