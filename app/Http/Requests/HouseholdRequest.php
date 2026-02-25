<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HouseholdRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'max:3'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
