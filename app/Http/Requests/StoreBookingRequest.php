<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'meeting_type' => ['required', 'string', 'in:offline,hybrid'],
            'participant_count' => ['required', 'integer', 'min:1'],
            'room_id' => ['required', 'exists:rooms,id'],
            'additional_requirements' => ['nullable', 'array'],
            'additional_requirements.*' => ['string', 'in:Konsumsi / Makan,Snack,Air Minum,Perlengkapan Meeting,Lainnya'],
            'additional_requirements_other' => ['nullable', 'string', 'max:1000'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }
}
