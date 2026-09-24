<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && ($user->isAdmin() || $user->isGa());
    }

    public function rules(): array
    {
        return [
            'plate_number' => ['required', 'string', 'max:20', 'unique:vehicles,plate_number'],
            'name'         => ['required', 'string', 'max:100'],
            'fuel_type'    => ['required', 'string', 'max:50'],
            'status'       => ['required', 'in:available,maintenance,inactive'],
            'notes'        => ['nullable', 'string'],
        ];
    }
}
