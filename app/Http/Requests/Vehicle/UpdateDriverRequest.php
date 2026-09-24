<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && ($user->isAdmin() || $user->isGa());
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:30'],
            'status'       => ['required', 'in:available,inactive'],
            'user_id'      => ['nullable', 'exists:users,id'],
            'notes'        => ['nullable', 'string'],
        ];
    }
}
