<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && ($user->isAdmin() || $user->isGa());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:facilities,name'],
            'description' => ['nullable', 'string'],
        ];
    }
}
