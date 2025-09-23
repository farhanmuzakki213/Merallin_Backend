<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLemburRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Izinkan semua user yang sudah terautentikasi untuk membuat request ini
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis_hari'        => 'required|in:Kerja,Libur,Libur Nasional',
            'department'        => 'required|in:Finance,HRD,IT,Admin,Manager Operasional',
            'tanggal_lembur'    => 'required|date',
            'keterangan_lembur' => 'required|string',
            'mulai_jam_lembur'  => 'required|date_format:H:i',
            'selesai_jam_lembur'=> 'required|date_format:H:i|after:mulai_jam_lembur',
        ];
    }
}
