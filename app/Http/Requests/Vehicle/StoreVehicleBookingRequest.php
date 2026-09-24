<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'purpose'        => ['required', 'string', 'max:1000'],
            'destination'    => ['required', 'string', 'max:255'],
            'start_datetime' => ['required', 'date', 'after_or_equal:now'],
            'end_datetime'   => ['required', 'date', 'after:start_datetime'],
            'vehicle_id'     => ['nullable', 'exists:vehicles,id'],
            'notes'          => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_datetime.after_or_equal' => 'Waktu keberangkatan tidak boleh di masa lalu.',
            'end_datetime.after'            => 'Waktu kembali harus setelah waktu keberangkatan.',
        ];
    }
}
