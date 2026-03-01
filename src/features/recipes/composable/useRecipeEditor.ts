import {computed, ref} from "vue";
import {createRecipe, updateRecipe} from "@/features/recipes/services/recipe.service.ts";
import type {
    Recipe,
    RecipeIngredient,
    RecipeIngredientInput,
    RecipeSourceType,
    RecipeStep,
    RecipeStepInput,
    RecipeWritePayload
} from "@/features/recipes/types/recipe.ts";

export interface RecipeEditorIngredientForm {
    ingredient_id?: number | null;
    ingredient_slug?: string | null;
    ingredient_text?: string | null;
    amount: string;
    unit: string;
    preparation_note: string;
    is_optional: boolean;
}

export interface RecipeEditorForm {
    title: string;
    description: string;
    prepTimeMinutes: string;
    cookTimeMinutes: string;
    servings: string;
    sourceType: RecipeSourceType;
    sourceUrl: string;
    nutritionRaw: string;
    tagsRaw: string;
    steps: RecipeStepInput[];
    ingredients: RecipeEditorIngredientForm[];
}

function defaultForm(): RecipeEditorForm {
    return {
        title: '',
        description: '',
        prepTimeMinutes: '',
        cookTimeMinutes: '',
        servings: '',
        sourceType: 'manual',
        sourceUrl: '',
        nutritionRaw: '{}',
        tagsRaw: '',
        steps: [{instruction: ''}],
        ingredients: [{ingredient_text: '', amount: '', unit: '', preparation_note: '', is_optional: false}],
    };
}

function toStepInput(step: RecipeStep): RecipeStepInput {
    return {
        position: step.position,
        instruction: step.instruction,
        timer_seconds: step.timer_seconds ?? null,
    };
}

function toIngredientInput(ingredient: RecipeIngredient): RecipeIngredientInput {
    return {
        position: ingredient.position,
        ingredient_id: ingredient.ingredient_id ?? null,
        ingredient_slug: ingredient.ingredient_slug ?? null,
        ingredient_text: ingredient.ingredient_text ?? null,
        amount: ingredient.amount ?? null,
        unit: ingredient.unit ?? null,
        preparation_note: ingredient.preparation_note ?? null,
        is_optional: ingredient.is_optional,
    };
}

function toEditorIngredientForm(ingredient: RecipeIngredient): RecipeEditorIngredientForm {
    const mapped = toIngredientInput(ingredient);

    return {
        ingredient_id: mapped.ingredient_id ?? null,
        ingredient_slug: mapped.ingredient_slug ?? null,
        ingredient_text: mapped.ingredient_text ?? null,
        amount: mapped.amount !== null && mapped.amount !== undefined ? String(mapped.amount) : '',
        unit: mapped.unit ?? '',
        preparation_note: mapped.preparation_note ?? '',
        is_optional: Boolean(mapped.is_optional),
    };
}

function parseInteger(value: string): number | null {
    const trimmed = value.trim();
    if (trimmed === '') {
        return null;
    }

    const parsed = Number(trimmed);
    if (!Number.isInteger(parsed) || parsed < 0) {
        return null;
    }

    return parsed;
}

function parsePositiveInteger(value: string): number | null {
    const parsed = parseInteger(value);
    if (parsed === null || parsed <= 0) {
        return null;
    }

    return parsed;
}

function parseTags(value: string): string[] {
    return value
        .split(',')
        .map((tag) => tag.trim())
        .filter((tag) => tag !== '');
}

function parseAmount(value: string): number | null {
    const trimmed = value.trim();
    if (trimmed === '') {
        return null;
    }

    const parsed = Number(trimmed);
    if (!Number.isFinite(parsed) || parsed < 0) {
        return null;
    }

    return parsed;
}

function normalizeIngredients(ingredients: RecipeEditorIngredientForm[]): RecipeIngredientInput[] {
    return ingredients
        .map((ingredient) => ({
            ingredient_id: ingredient.ingredient_id ?? null,
            ingredient_slug: ingredient.ingredient_slug?.trim() || null,
            ingredient_text: ingredient.ingredient_text?.trim() || null,
            amount: parseAmount(ingredient.amount),
            unit: ingredient.unit?.trim() || null,
            preparation_note: ingredient.preparation_note?.trim() || null,
            is_optional: Boolean(ingredient.is_optional),
        }))
        .filter((ingredient) => {
            return Boolean(
                ingredient.ingredient_id ||
                ingredient.ingredient_slug ||
                ingredient.ingredient_text
            );
        });
}

function normalizeSteps(steps: RecipeStepInput[]): RecipeStepInput[] {
    return steps
        .map((step) => ({
            instruction: step.instruction.trim(),
            timer_seconds: step.timer_seconds ?? null,
        }))
        .filter((step) => step.instruction !== '');
}

export function useRecipeEditor() {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);
    const currentRecipeId = ref<number | null>(null);
    const form = ref<RecipeEditorForm>(defaultForm());

    const canSubmit = computed<boolean>(() => {
        return !loading.value && form.value.title.trim() !== '';
    });

    function resetForm() {
        form.value = defaultForm();
        currentRecipeId.value = null;
        error.value = null;
    }

    function loadRecipe(recipe: Recipe) {
        currentRecipeId.value = recipe.id;
        form.value = {
            title: recipe.title,
            description: recipe.description ?? '',
            prepTimeMinutes: recipe.prep_time_minutes?.toString() ?? '',
            cookTimeMinutes: recipe.cook_time_minutes?.toString() ?? '',
            servings: recipe.servings?.toString() ?? '',
            sourceType: recipe.source_type,
            sourceUrl: recipe.source_url ?? '',
            nutritionRaw: JSON.stringify(recipe.nutrition ?? {}, null, 2),
            tagsRaw: (recipe.tags ?? []).map((tag) => tag.name).join(', '),
            steps: (recipe.steps ?? []).map(toStepInput),
            ingredients: (recipe.ingredients ?? []).map(toEditorIngredientForm),
        };

        if (form.value.steps.length === 0) {
            form.value.steps.push({instruction: ''});
        }

        if (form.value.ingredients.length === 0) {
            form.value.ingredients.push({ingredient_text: '', amount: '', unit: '', preparation_note: '', is_optional: false});
        }

        error.value = null;
    }

    function addStep() {
        form.value.steps.push({instruction: ''});
    }

    function removeStep(index: number) {
        if (form.value.steps.length <= 1) {
            form.value.steps[0] = {instruction: ''};
            return;
        }

        form.value.steps.splice(index, 1);
    }

    function addIngredient() {
        form.value.ingredients.push({ingredient_text: '', amount: '', unit: '', preparation_note: '', is_optional: false});
    }

    function removeIngredient(index: number) {
        if (form.value.ingredients.length <= 1) {
            form.value.ingredients[0] = {ingredient_text: '', amount: '', unit: '', preparation_note: '', is_optional: false};
            return;
        }

        form.value.ingredients.splice(index, 1);
    }

    function toPayload(): RecipeWritePayload {
        let nutrition: Record<string, unknown> = {};

        const nutritionRaw = form.value.nutritionRaw.trim();
        if (nutritionRaw !== '') {
            try {
                const parsed = JSON.parse(nutritionRaw) as Record<string, unknown>;
                nutrition = parsed && typeof parsed === 'object' ? parsed : {};
            } catch {
                throw new Error('Nutrition must be valid JSON.');
            }
        }

        const steps = normalizeSteps(form.value.steps);
        if (steps.length === 0) {
            throw new Error('At least one recipe step is required.');
        }

        const ingredients = normalizeIngredients(form.value.ingredients);
        if (ingredients.length === 0) {
            throw new Error('At least one recipe ingredient is required.');
        }

        const prepTime = parseInteger(form.value.prepTimeMinutes);
        const cookTime = parseInteger(form.value.cookTimeMinutes);
        const servings = parsePositiveInteger(form.value.servings);

        if (form.value.servings.trim() !== '' && servings === null) {
            throw new Error('Servings must be a whole number greater than 0.');
        }

        if (form.value.prepTimeMinutes.trim() !== '' && prepTime === null) {
            throw new Error('Prep time must be a whole number 0 or greater.');
        }

        if (form.value.cookTimeMinutes.trim() !== '' && cookTime === null) {
            throw new Error('Cook time must be a whole number 0 or greater.');
        }

        return {
            title: form.value.title.trim(),
            description: form.value.description.trim() || null,
            prep_time_minutes: prepTime,
            cook_time_minutes: cookTime,
            servings,
            source_type: form.value.sourceType,
            source_url: form.value.sourceUrl.trim() || null,
            nutrition,
            tags: parseTags(form.value.tagsRaw),
            steps,
            ingredients,
        };
    }

    async function submit(): Promise<Recipe | null> {
        if (!canSubmit.value) {
            return null;
        }

        error.value = null;
        loading.value = true;

        try {
            const payload = toPayload();
            if (currentRecipeId.value) {
                return await updateRecipe(currentRecipeId.value, payload);
            }

            return await createRecipe(payload);
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'Failed to save recipe.';
            return null;
        } finally {
            loading.value = false;
        }
    }

    return {
        loading,
        error,
        currentRecipeId,
        form,
        canSubmit,
        resetForm,
        loadRecipe,
        addStep,
        removeStep,
        addIngredient,
        removeIngredient,
        submit,
    };
}
