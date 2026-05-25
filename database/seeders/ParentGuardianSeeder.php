<?php

namespace Database\Seeders;

use App\Models\ParentGuardian;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentGuardianSeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            [
                'email' => 'parent1@example.com',
                'user_name' => 'Ahmad Wijaya',
                'phone_num' => '+6281234567891',
                'father_name' => 'Ahmad Wijaya',
                'mother_name' => 'Siti Nurhaliza',
                'father_phone_num' => '+6281234567891',
                'mother_phone_num' => '+6281234567892',
                'father_occupation' => 'Insinyur',
                'mother_occupation' => 'Guru',
                'father_address' => 'Jl. Merdeka No. 123, Bandung',
                'mother_address' => 'Jl. Merdeka No. 123, Bandung',
            ],
            [
                'email' => 'parent2@example.com',
                'user_name' => 'Bambang Suryanto',
                'phone_num' => '+6281234567893',
                'father_name' => 'Bambang Suryanto',
                'mother_name' => 'Dewi Lestari',
                'father_phone_num' => '+6281234567893',
                'mother_phone_num' => '+6281234567894',
                'father_occupation' => 'Pengusaha',
                'mother_occupation' => 'Dokter',
                'father_address' => 'Jl. Gatot Subroto No. 45, Bandung',
                'mother_address' => 'Jl. Gatot Subroto No. 45, Bandung',
            ],
            [
                'email' => 'parent3@example.com',
                'user_name' => 'Rudi Hermawan',
                'phone_num' => '+6281234567895',
                'father_name' => 'Rudi Hermawan',
                'mother_name' => 'Rita Zahara',
                'father_phone_num' => '+6281234567895',
                'mother_phone_num' => '+6281234567896',
                'father_occupation' => 'Politisi',
                'mother_occupation' => 'Ibu Rumah Tangga',
                'father_address' => 'Jl. Dago No. 78, Bandung',
                'mother_address' => 'Jl. Dago No. 78, Bandung',
            ],
        ];

        foreach ($parents as $parent) {
            $user = User::query()->updateOrCreate(
                ['email' => $parent['email']],
                [
                    'name' => $parent['user_name'],
                    'phone_num' => $parent['phone_num'],
                    'role' => 'guest',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            ParentGuardian::query()->updateOrCreate(
                ['id_user' => $user->id],
                [
                    'father_name' => $parent['father_name'],
                    'mother_name' => $parent['mother_name'],
                    'father_phone_num' => $parent['father_phone_num'],
                    'mother_phone_num' => $parent['mother_phone_num'],
                    'father_occupation' => $parent['father_occupation'],
                    'mother_occupation' => $parent['mother_occupation'],
                    'father_address' => $parent['father_address'],
                    'mother_address' => $parent['mother_address'],
                ]
            );
        }
    }
}
