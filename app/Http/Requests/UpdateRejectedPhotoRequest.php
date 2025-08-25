<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRejectedPhotoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $trip = $this->route('trip');
        return $this->user()->id === $trip->user_id;
    }

    /**
     * Secara dinamis membuat aturan validasi berdasarkan status foto di database.
     */
    public function rules(): array
    {
        /** @var Trip $trip */
        $trip = $this->route('trip');
        $rules = [];

       // --- Validasi untuk Foto Tunggal ---
        $rules['start_km_photo'] = [
            Rule::requiredIf($trip->start_km_photo_status === 'rejected'),
            Rule::prohibitedIf($trip->start_km_photo_status !== 'rejected'), // [BARU]
            'image', 'max:5120'
        ];
        $rules['muat_photo'] = [
            Rule::requiredIf($trip->muat_photo_status === 'rejected'),
            Rule::prohibitedIf($trip->muat_photo_status !== 'rejected'), // [BARU]
            'image', 'max:5120'
        ];
        $rules['end_km_photo'] = [
            Rule::requiredIf($trip->end_km_photo_status === 'rejected'),
            Rule::prohibitedIf($trip->end_km_photo_status !== 'rejected'), // [BARU]
            'image', 'max:5120'
        ];
        $rules['delivery_order'] = [
            Rule::requiredIf($trip->delivery_order_status === 'rejected'),
            Rule::prohibitedIf($trip->delivery_order_status !== 'rejected'), // [BARU]
            'image', 'max:5120'
        ];
        $rules['timbangan_kendaraan_photo'] = [
            Rule::requiredIf($trip->timbangan_kendaraan_photo_status === 'rejected'),
            Rule::prohibitedIf($trip->timbangan_kendaraan_photo_status !== 'rejected'), // [BARU]
            'image', 'max:5120'
        ];
        $rules['segel_photo'] = [
            Rule::requiredIf($trip->segel_photo_status === 'rejected'),
            Rule::prohibitedIf($trip->segel_photo_status !== 'rejected'), // [BARU]
            'image', 'max:5120'
        ];

        // --- Validasi untuk Foto Array ---
        $rules['bongkar_photo'] = [
            Rule::requiredIf($trip->bongkar_photo_status === 'rejected'),
            Rule::prohibitedIf($trip->bongkar_photo_status !== 'rejected'), // [BARU]
            'array', 'min:1'
        ];
        $rules['bongkar_photo.*'] = ['image', 'max:5120'];

        $rules['initial_delivery_letters'] = [
            Rule::requiredIf($trip->delivery_letter_initial_status === 'rejected'),
            Rule::prohibitedIf($trip->delivery_letter_initial_status !== 'rejected'), // [BARU]
            'array', 'min:1'
        ];
        $rules['initial_delivery_letters.*'] = ['image', 'max:5120'];

        $rules['final_delivery_letters'] = [
            Rule::requiredIf($trip->delivery_letter_final_status === 'rejected'),
            Rule::prohibitedIf($trip->delivery_letter_final_status !== 'rejected'), // [BARU]
            'array', 'min:1'
        ];
        $rules['final_delivery_letters.*'] = ['image', 'max:5120'];

        return $rules;
    }

    /**
     * Pesan error yang spesifik untuk setiap field.
     */
    public function messages(): array
    {
        return [
            'required'   => ':attribute sebelumnya ditolak. Anda wajib mengunggah file baru.',
            'prohibited' => 'Aksi ditolak. :attribute tidak sedang dalam status ditolak sehingga tidak dapat diubah.',
            'array'      => ':attribute harus dalam format array.',
            'min'        => ':attribute harus memiliki minimal :min item.',
            'image'      => ':attribute harus berupa file gambar.',
            'max'        => ':attribute tidak boleh lebih dari 5MB.',
        ];
    }
}
