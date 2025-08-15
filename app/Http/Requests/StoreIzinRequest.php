<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIzinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Otorisasi sudah ditangani oleh middleware auth:sanctum di route
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
            'jenis_izin'      => ['required', Rule::in(['Sakit', 'Kepentingan Keluarga'])],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan'          => ['nullable', 'string', 'max:1000'],
            'bukti'           => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // file bukti, max 2MB
        ];
    }
}
