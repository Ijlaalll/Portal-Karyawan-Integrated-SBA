<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleFuelLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && $user->isGa();
    }

    public function rules(): array
    {
        return [
            'vehicle_id'  => ['required', 'exists:vehicles,id'],
            'recorded_at' => ['required', 'date'],
            'odometer'    => ['required', 'integer', 'min:0'],
            'fuel_liters' => ['required', 'numeric', 'gt:0'],
            'fuel_cost'   => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_id.required'  => 'Kendaraan wajib dipilih.',
            'vehicle_id.exists'    => 'Kendaraan yang dipilih tidak valid.',
            'recorded_at.required' => 'Tanggal & waktu pengisian wajib diisi.',
            'recorded_at.date'     => 'Format tanggal & waktu pengisian tidak valid.',
            'odometer.required'    => 'Odometer saat pengisian wajib diisi.',
            'odometer.integer'     => 'Odometer harus berupa bilangan bulat (km).',
            'odometer.min'         => 'Odometer tidak boleh bernilai negatif.',
            'fuel_liters.required' => 'Jumlah BBM (liter) wajib diisi.',
            'fuel_liters.numeric'  => 'Jumlah BBM harus berupa angka.',
            'fuel_liters.gt'       => 'Jumlah BBM harus lebih dari 0 liter.',
            'fuel_cost.numeric'    => 'Biaya BBM harus berupa angka.',
            'fuel_cost.min'        => 'Biaya BBM tidak boleh bernilai negatif.',
        ];
    }
}
