<?php

namespace App\Http\Requests;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'company' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'unit_type' => ['required', Rule::in(array_column(UnitType::cases(), 'value'))],
            'unit_options' => ['nullable', 'array'],
            'unit_options.*' => ['string', 'max:32'],
            'unit_default' => [
                'required',
                'string',
                'max:32',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
