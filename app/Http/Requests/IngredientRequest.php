<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        if (is_string($name)) {
            $this->merge(['name' => mb_strtolower(trim($name))]);
        }

        $categorySlug = $this->input('category_slug');
        if (is_string($categorySlug) && trim($categorySlug) !== '' && ! $this->filled('category_id')) {
            $slug = trim($categorySlug);
            $categoryId = Category::query()->where('slug', $slug)->value('id');

            $this->merge([
                'category_id' => $categoryId,
                'category' => $slug,
                'category_slug' => $slug,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category_slug' => ['nullable', 'string', 'max:255', 'exists:categories,slug'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
