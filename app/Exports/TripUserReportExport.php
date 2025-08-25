<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TripUserReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
    * @return array
    */
    public function array(): array
    {
        return $this->data;
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        // Mendefinisikan judul kolom di file Excel
        return [
            'Driver',
            'Bulan',
            'Trip Muatan Perusahaan',
            'Trip Muatan Driver',
            'Total Trip Selesai',
        ];
    }
}
