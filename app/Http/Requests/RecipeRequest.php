<?php

namespace App\Http\Requests;

use App\Models\Ingredient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RecipeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $ingredients = $this->input('ingredients');
        if (!is_array($ingredients)) {
            return;
        }

        $normalized = [];

        foreach ($ingredients as $ingredient) {
            if (!is_array($ingredient)) {
                continue;
            }

            $row = $ingredient;
            $ingredientSlug = $row['ingredient_slug'] ?? null;
            if (is_string($ingredientSlug) && trim($ingredientSlug) !== '' && empty($row['ingredient_id'])) {
                $slug = trim($ingredientSlug);
                $ingredientId = Ingredient::query()
                    ->where('slug', $slug)
                    ->value('id');

                $row['ingredient_slug'] = $slug;
                $row['ingredient_id'] = $ingredientId;
            }

            $normalized[] = $row;
        }

        $this->merge(['ingredients' => $normalized]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'servings' => ['nullable', 'integer', 'min:1', 'max:100'],
            'source_type' => ['nullable', Rule::in(['manual', 'site_import', 'ai_generated'])],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'nutrition' => ['nullable', 'array'],

            'steps' => ['required', 'array', 'min:1', 'max:100'],
            'steps.*.position' => ['nullable', 'integer', 'min:1'],
            'steps.*.instruction' => ['required', 'string', 'min:2', 'max:1000'],
            'steps.*.timer_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],

            'ingredients' => ['required', 'array', 'min:1', 'max:200'],
            'ingredients.*.position' => ['nullable', 'integer', 'min:1'],
            'ingredients.*.ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'ingredients.*.ingredient_slug' => ['nullable', 'string', 'max:255', 'exists:ingredients,slug'],
            'ingredients.*.ingredient_text' => ['nullable', 'string', 'min:1', 'max:255'],
            'ingredients.*.amount' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:32'],
            'ingredients.*.preparation_note' => ['nullable', 'string', 'max:512'],
            'ingredients.*.is_optional' => ['nullable', 'boolean'],

            'tags' => ['nullable', 'array', 'max:25'],
            'tags.*' => ['string', 'min:1', 'max:64', 'distinct:ignore_case'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $ingredients = $this->input('ingredients', []);
            if (!is_array($ingredients)) {
                return;
            }

            foreach ($ingredients as $index => $ingredientRow) {
                if (!is_array($ingredientRow)) {
                    continue;
                }

                $hasIngredient =
                    !empty($ingredientRow['ingredient_id']) ||
                    !empty($ingredientRow['ingredient_slug']) ||
                    !empty(trim((string)($ingredientRow['ingredient_text'] ?? '')));

                if (!$hasIngredient) {
                    $validator->errors()->add(
                        'ingredients.' . $index,
                        'Each ingredient row must include ingredient_id, ingredient_slug, or ingredient_text.'
                    );
                }
            }
        });
    }

    public function authorize(): bool
    {
        return true;
    }
}
