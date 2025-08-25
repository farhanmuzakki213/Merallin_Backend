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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validPhotoTypes = [
            'start_km_photo', 'muat_photo', 'end_km_photo', 'delivery_order',
            'timbangan_kendaraan_photo', 'segel_photo', 'bongkar_photo', 'delivery_letters'
        ];
        return [
            'photo_type' => ['required', 'string', Rule::in($validPhotoTypes)],
            'photo_file' => 'required|file|image|max:5120',
            'old_photo_path' => ['nullable', 'string', Rule::requiredIf(fn () => in_array($this->input('photo_type'), ['bongkar_photo', 'delivery_letters']))],
            'letter_type'    => ['nullable', 'string', Rule::in(['initial', 'final']), Rule::requiredIf(fn () => $this->input('photo_type') === 'delivery_letters')],
        ];
    }
}
