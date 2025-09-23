<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Daftar hari libur nasional (YYYY-MM-DD).
     * Anda bisa menambahkan data tahun-tahun berikutnya di sini.
     */
    private static function getNationalHolidays(int $year): array
    {
        return [
            // Contoh data untuk 2024
            '2024-01-01', // Tahun Baru Masehi
            '2024-02-08', // Isra Mi'raj
            '2024-02-10', // Tahun Baru Imlek
            '2024-03-11', // Hari Suci Nyepi
            '2024-03-29', // Wafat Isa Al Masih
            '2024-03-31', // Hari Paskah
            '2024-04-10', // Idul Fitri
            '2024-04-11', // Idul Fitri
            '2024-05-01', // Hari Buruh
            '2024-05-09', // Kenaikan Isa Al Masih
            '2024-05-23', // Hari Waisak
            '2024-06-01', // Hari Lahir Pancasila
            '2024-06-17', // Idul Adha
            '2024-07-07', // Tahun Baru Islam
            '2024-08-17', // Hari Kemerdekaan
            '2024-09-16', // Maulid Nabi Muhammad SAW
            '2024-12-25', // Hari Natal

            // Data untuk 2025 (bisa ditambahkan dari kalender resmi)
            '2025-01-01', // Tahun Baru Masehi
            '2025-01-27', // Isra Mi'raj
            '2025-01-29', // Tahun Baru Imlek
            '2025-03-01', // Hari Suci Nyepi
            '2025-03-30', // Idul Fitri
            '2025-03-31', // Idul Fitri
            '2025-04-18', // Wafat Isa Al Masih
            '2025-04-20', // Hari Paskah
            '2025-05-01', // Hari Buruh
            '2025-05-12', // Hari Waisak
            '2025-05-29', // Kenaikan Isa Al Masih
            '2025-06-01', // Hari Lahir Pancasila
            '2025-06-06', // Idul Adha
            '2025-06-26', // Tahun Baru Islam
            '2025-08-17', // Hari Kemerdekaan
            '2025-09-05', // Maulid Nabi Muhammad SAW
            '2025-12-25', // Hari Natal
        ];
    }

    /**
     * Cek apakah sebuah tanggal adalah hari libur nasional.
     */
    public static function isNationalHoliday(Carbon $date): bool
    {
        $holidays = self::getNationalHolidays($date->year);
        return in_array($date->format('Y-m-d'), $holidays);
    }
}
