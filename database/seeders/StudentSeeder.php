<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $parents = ParentGuardian::all();

        if ($parents->count() < 3) {
            return;
        }

        $students = [
            [
                'id_parents' => $parents[0]->id_parents,
                'name' => 'Aldi Wijaya',
                'birth_place' => 'Bandung',
                'birth_date' => '2020-05-15',
                'gender' => 'pria',
                'group' => 'A',
                'status' => 'aktif',
            ],
            [
                'id_parents' => $parents[0]->id_parents,
                'name' => 'Arisya Wijaya',
                'birth_place' => 'Bandung',
                'birth_date' => '2021-08-22',
                'gender' => 'perempuan',
                'group' => 'B',
                'status' => 'aktif',
            ],
            [
                'id_parents' => $parents[1]->id_parents,
                'name' => 'Bintang Suryanto',
                'birth_place' => 'Jakarta',
                'birth_date' => '2020-11-10',
                'gender' => 'pria',
                'group' => 'A',
                'status' => 'aktif',
            ],
            [
                'id_parents' => $parents[2]->id_parents,
                'name' => 'Cantika Hermawan',
                'birth_place' => 'Bandung',
                'birth_date' => '2021-03-18',
                'gender' => 'perempuan',
                'group' => 'C',
                'status' => 'aktif',
            ],
        ];

        foreach ($students as $student) {
            Student::query()->updateOrCreate(
                [
                    'id_parents' => $student['id_parents'],
                    'name' => $student['name'],
                ],
                [
                    'birth_place' => $student['birth_place'],
                    'birth_date' => $student['birth_date'],
                    'gender' => $student['gender'],
                    'group' => $student['group'],
                    'status' => $student['status'],
                    'id_registration' => null,
                ]
            );
        }
    }
}
