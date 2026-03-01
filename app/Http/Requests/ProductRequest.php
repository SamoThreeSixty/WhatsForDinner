<?php

namespace App\Http\Requests;

use App\Enums\UnitType;
use App\Models\Ingredient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $ingredientSlug = $this->input('ingredient_slug');
        if (is_string($ingredientSlug) && trim($ingredientSlug) !== '' && ! $this->filled('ingredient_id')) {
            $ingredientId = Ingredient::query()
                ->where('slug', trim($ingredientSlug))
                ->value('id');

            $this->merge([
                'ingredient_id' => $ingredientId,
                'ingredient_slug' => trim($ingredientSlug),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'ingredient_id' => ['required_without:ingredient_slug', 'integer', 'exists:ingredients,id'],
            'ingredient_slug' => ['required_without:ingredient_id', 'string', 'max:255', 'exists:ingredients,slug'],
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
