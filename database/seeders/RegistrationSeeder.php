<?php

namespace Database\Seeders;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $registrants = [
            [
                'email' => 'registrant1@example.com',
                'name' => 'Orang Tua 1',
                'phone_num' => '+628111111111',
                'candidate_data' => [
                    'name' => 'Anak 1',
                    'birth_place' => 'Bandung',
                    'birth_date' => '2021-01-10',
                    'gender' => 'perempuan',
                ],
                'parents_data' => [
                    'father_name' => 'Ayah 1',
                    'mother_name' => 'Ibu 1',
                    'father_phone_num' => '+628111111112',
                    'mother_phone_num' => '+628111111113',
                    'father_occupation' => 'Karyawan',
                    'mother_occupation' => 'Ibu Rumah Tangga',
                    'father_address' => 'Bandung',
                    'mother_address' => 'Bandung',
                ],
                'group' => 'A',
                'status' => 'pending',
            ],
            [
                'email' => 'registrant2@example.com',
                'name' => 'Orang Tua 2',
                'phone_num' => '+628222222222',
                'candidate_data' => [
                    'name' => 'Anak 2',
                    'birth_place' => 'Bandung',
                    'birth_date' => '2020-09-22',
                    'gender' => 'pria',
                ],
                'parents_data' => [
                    'father_name' => 'Ayah 2',
                    'mother_name' => 'Ibu 2',
                    'father_phone_num' => '+628222222223',
                    'mother_phone_num' => '+628222222224',
                    'father_occupation' => 'Wiraswasta',
                    'mother_occupation' => 'Guru',
                    'father_address' => 'Bandung',
                    'mother_address' => 'Bandung',
                ],
                'group' => 'B',
                'status' => 'approved_awaiting_payment',
            ],
            [
                'email' => 'registrant3@example.com',
                'name' => 'Orang Tua 3',
                'phone_num' => '+628333333333',
                'candidate_data' => [
                    'name' => 'Anak 3',
                    'birth_place' => 'Bandung',
                    'birth_date' => '2021-05-05',
                    'gender' => 'perempuan',
                ],
                'parents_data' => [
                    'father_name' => 'Ayah 3',
                    'mother_name' => 'Ibu 3',
                    'father_phone_num' => '+628333333334',
                    'mother_phone_num' => '+628333333335',
                    'father_occupation' => 'Buruh',
                    'mother_occupation' => 'Ibu Rumah Tangga',
                    'father_address' => 'Bandung',
                    'mother_address' => 'Bandung',
                ],
                'group' => 'A',
                'status' => 'rejected',
            ],
        ];

        foreach ($registrants as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone_num' => $data['phone_num'],
                    'role' => 'guest',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Avoid duplicate registrations per user on repeated seeding
            if (Registration::query()->where('id_user', $user->id)->exists()) {
                continue;
            }

            Registration::query()->create([
                'id_user' => $user->id,
                'candidate_data' => $data['candidate_data'],
                'parents_data' => $data['parents_data'],
                'group' => $data['group'],
                'status' => $data['status'],
                'payment_deadline' => null,
                'grace_period_until' => null,
                'paid_late' => false,
                'reject_reason' => $data['status'] === 'rejected' ? 'Dokumen tidak lengkap' : null,
            ]);
        }
    }
}
