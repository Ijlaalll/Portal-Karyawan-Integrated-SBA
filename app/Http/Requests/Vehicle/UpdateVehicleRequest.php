<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && ($user->isAdmin() || $user->isGa());
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle') instanceof \App\Models\Vehicle
            ? $this->route('vehicle')->id
            : $this->route('vehicle');

        return [
            'plate_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'plate_number')->ignore($vehicleId),
            ],
            'name'      => ['required', 'string', 'max:100'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'status'    => ['required', 'in:available,maintenance,inactive'],
            'notes'     => ['nullable', 'string'],
        ];
    }
}
