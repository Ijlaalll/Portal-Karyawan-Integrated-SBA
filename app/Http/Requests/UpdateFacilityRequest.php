<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && ($user->isAdmin() || $user->isGa());
    }

    public function rules(): array
    {
        $facilityId = $this->route('facility') ? $this->route('facility')->id : null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('facilities', 'name')->ignore($facilityId)],
            'description' => ['nullable', 'string'],
        ];
    }
}
