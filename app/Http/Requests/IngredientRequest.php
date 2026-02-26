<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'unit' => ['required'],
            'quantity' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
