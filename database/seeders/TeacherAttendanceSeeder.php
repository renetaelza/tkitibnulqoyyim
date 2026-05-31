<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\TeacherAttendance;
use App\Models\TeacherDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TeacherAttendanceSeeder extends Seeder
{
    /**
     * Seed absensi guru Jan–Mei 2026 untuk pengujian fitur honor guru.
     *
     * Pola per guru dirancang untuk meng-cover semua skenario perhitungan honor:
     *   - hadir biasa        → dihitung sebagai hadir efektif × rate
     *   - izin ≤ 2 hari      → tidak kena potongan (dalam grace period)
     *   - izin > 2 hari berturut → kena permission_penalty per hari kelebihan
     *   - terlambat           → kena late_penalty per kejadian
     *   - sakit               → tidak hadir, tidak kena potongan izin
     *   - alpa                → tidak hadir, tidak kena potongan izin
     *   - hari libur nasional → otomatis kredit hadir (tidak perlu absen)
     *
     * DEV NOTE: Seeder ini men-truncate data absensi yang ada sebelum re-seed.
     */
    public function run(): void
    {
        $emails = [
            'teacher@example.com',
            'teacher2@example.com',
            'teacher3@example.com',
            'headmaster@example.com',
        ];

        $teachers = TeacherDetail::query()
            ->whereHas('user', fn($q) => $q->whereIn('email', $emails))
            ->with('user')
            ->get()
            ->keyBy(fn($t) => $t->user->email);

        if ($teachers->isEmpty()) {
            return;
        }

        // Hapus data lama agar re-seed menghasilkan data yang bersih.
        TeacherAttendance::query()
            ->whereIn('id_teacher', $teachers->pluck('id_teacher'))
            ->delete();

        // Ambil semua tanggal libur (non-weekend) Jan–Mei 2026 untuk di-skip.
        $holidayDates = Holiday::query()
            ->active()
            ->whereBetween('date', ['2026-01-01', '2026-05-31'])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->flip(); // key = date string → O(1) lookup

        /*
         * Exception map: tanggal yang bukan 'hadir tepat waktu'.
         * Format: 'YYYY-MM-DD' => [status, is_late, late_minutes]
         *
         * Skenario yang di-cover setiap guru:
         *
         * Ibu Samiyah (teacher) — Wali Kelas A, rate Rp 75.000
         *   - Izin tersebar (1–2 per bulan, tidak consecutive → tidak kena potongan izin)
         *   - 2 sakit, 1 alpa
         *   - 3 kali terlambat (trigger late_penalty Rp 10.000 × 3)
         *
         * Bapak Ahmad (headmaster) — Kepala Sekolah, rate Rp 100.000
         *   - Kehadiran sangat baik
         *   - 2 izin (tidak berurutan), 1 sakit, 1 terlambat
         *
         * Bapak Rahmat (teacher2) — Wali Kelas B + Bendahara, rate Rp 75.000
         *   - 3 izin berurutan Jan (Senin–Rabu 19–21 Jan) → grace 2, penalty 1 hari
         *   - 4 izin berurutan Apr (Senin–Kamis 6–9 Apr) → grace 2, penalty 2 hari
         *   - 2 terlambat, 1 sakit, 1 alpa
         *
         * Ibu Yanti (teacher3) — Guru Pendamping, rate Rp 65.000
         *   - Kehadiran paling rendah
         *   - 4 izin berurutan Feb (9–12 Feb) → penalty 2 hari
         *   - 2 alpa, 2 sakit
         *   - 4 kali terlambat
         */
        $exceptions = [

            // ── Ibu Samiyah ─────────────────────────────────────────────────
            'teacher@example.com' => [
                // Januari: 1 izin, 1 terlambat
                '2026-01-07' => ['izin',  false,  0],
                '2026-01-14' => ['hadir', true,  18],  // telat 18 menit
                // Februari: 1 sakit, 1 izin
                '2026-02-04' => ['sakit', false,  0],
                '2026-02-18' => ['izin',  false,  0],
                // Maret: 1 alpa, 1 terlambat
                '2026-03-05' => ['alpa',  false,  0],
                '2026-03-19' => ['hadir', true,  25],  // telat 25 menit
                // April: 1 izin, 1 sakit
                '2026-04-08' => ['izin',  false,  0],
                '2026-04-23' => ['sakit', false,  0],
                // Mei: 1 izin, 1 terlambat
                '2026-05-06' => ['izin',  false,  0],
                '2026-05-20' => ['hadir', true,  12],  // telat 12 menit
            ],

            // ── Bapak Ahmad Fauzi (Kepala Sekolah) ──────────────────────────
            'headmaster@example.com' => [
                // Januari: 1 izin
                '2026-01-26' => ['izin',  false,  0],
                // Februari: 1 sakit, 1 terlambat
                '2026-02-11' => ['sakit', false,  0],
                '2026-02-25' => ['hadir', true,  10],  // telat 10 menit
                // Maret: 1 izin
                '2026-03-11' => ['izin',  false,  0],
                // April: -
                // Mei: 1 izin
                '2026-05-13' => ['izin',  false,  0],
            ],

            // ── Bapak Rahmat Hidayat (teacher2) ─────────────────────────────
            'teacher2@example.com' => [
                // Januari: 3 izin berurutan (Mon–Wed 19–21 Jan) → penalty 1 hari
                '2026-01-19' => ['izin',  false,  0],
                '2026-01-20' => ['izin',  false,  0],
                '2026-01-21' => ['izin',  false,  0],
                // Februari: 1 sakit, 1 terlambat
                '2026-02-05' => ['sakit', false,  0],
                '2026-02-24' => ['hadir', true,  22],  // telat 22 menit
                // Maret: 1 alpa
                '2026-03-17' => ['alpa',  false,  0],
                // April: 4 izin berurutan (Mon–Thu 6–9 Apr) → penalty 2 hari, 1 terlambat
                '2026-04-06' => ['izin',  false,  0],
                '2026-04-07' => ['izin',  false,  0],
                '2026-04-08' => ['izin',  false,  0],
                '2026-04-09' => ['izin',  false,  0],
                '2026-04-29' => ['hadir', true,  30],  // telat 30 menit
                // Mei: 1 izin
                '2026-05-07' => ['izin',  false,  0],
            ],

            // ── Ibu Yanti Rahayu (teacher3) ─────────────────────────────────
            'teacher3@example.com' => [
                // Januari: 1 sakit, 1 alpa, 1 terlambat
                '2026-01-08' => ['sakit', false,  0],
                '2026-01-20' => ['alpa',  false,  0],
                '2026-01-27' => ['hadir', true,  35],  // telat 35 menit
                // Februari: 4 izin berurutan (Mon–Thu 9–12 Feb) → penalty 2 hari
                '2026-02-09' => ['izin',  false,  0],
                '2026-02-10' => ['izin',  false,  0],
                '2026-02-11' => ['izin',  false,  0],
                '2026-02-12' => ['izin',  false,  0],
                '2026-02-20' => ['hadir', true,  20],  // telat 20 menit
                // Maret: 1 alpa, 1 sakit, 1 terlambat
                '2026-03-10' => ['alpa',  false,  0],
                '2026-03-18' => ['sakit', false,  0],
                '2026-03-26' => ['hadir', true,  15],  // telat 15 menit
                // April: 1 izin, 1 terlambat
                '2026-04-14' => ['izin',  false,  0],
                '2026-04-22' => ['hadir', true,  28],  // telat 28 menit
                // Mei: 1 izin, 1 sakit
                '2026-05-05' => ['izin',  false,  0],
                '2026-05-21' => ['sakit', false,  0],
            ],
        ];

        // Waktu check-in default per guru (variasi agar terasa natural)
        $defaultCheckInMinute = [
            'teacher@example.com'    => 35,  // 07:35
            'headmaster@example.com' => 30,  // 07:30
            'teacher2@example.com'   => 40,  // 07:40
            'teacher3@example.com'   => 45,  // 07:45
        ];

        $months = [
            Carbon::create(2026, 1, 1),
            Carbon::create(2026, 2, 1),
            Carbon::create(2026, 3, 1),
            Carbon::create(2026, 4, 1),
            Carbon::create(2026, 5, 1),
        ];

        $records = [];

        foreach ($months as $month) {
            $day = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            while ($day->lte($end)) {
                $dateStr = $day->toDateString();

                // Lewati Sabtu, Minggu, dan hari libur nasional
                if ($day->isWeekend() || isset($holidayDates[$dateStr])) {
                    $day->addDay();
                    continue;
                }

                foreach ($teachers as $email => $teacher) {
                    $exc = $exceptions[$email][$dateStr] ?? null;

                    [$status, $isLate, $lateMinutes] = $exc ?? ['hadir', false, 0];

                    $checkIn = null;
                    if ($status === 'hadir') {
                        if ($isLate) {
                            // Terlambat: check-in 08:00 + lateMinutes
                            $checkIn = $day->copy()->setTime(8, $lateMinutes);
                        } else {
                            // Tepat waktu: check-in antara 07:30–07:55
                            $checkIn = $day->copy()->setTime(7, $defaultCheckInMinute[$email] ?? 35);
                        }
                    }

                    $records[] = [
                        'id_teacher'      => $teacher->id_teacher,
                        'date'            => $dateStr,
                        'check_in_time'   => $checkIn?->toDateTimeString(),
                        'is_late'         => $isLate,
                        'late_minutes'    => $lateMinutes,
                        'status'          => $status,
                        'information'     => $status !== 'hadir' ? ucfirst($status) . ' - seeder' : null,
                        'attachment_path' => null,
                        'source'          => 'admin',
                        'created_at'      => now()->toDateTimeString(),
                        'updated_at'      => now()->toDateTimeString(),
                    ];
                }

                $day->addDay();
            }
        }

        // Insert dalam satu kali query (lebih cepat)
        foreach (array_chunk($records, 200) as $chunk) {
            TeacherAttendance::insert($chunk);
        }

        $total = count($records);
        $this->command->getOutput()->writeln("  <info>Inserted {$total} attendance records (Jan–May 2026)</info>");
    }
}
