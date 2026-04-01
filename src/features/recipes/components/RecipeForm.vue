<script setup lang="ts">
import Label from '@/components/ui/Label.vue';
import Input from '@/components/ui/Input.vue';
import Button from '@/components/ui/Button.vue';
import type {RecipeEditorForm} from '@/features/recipes/composable/useRecipeEditor.ts';

const props = defineProps<{
    form: RecipeEditorForm;
    loading: boolean;
    error: string | null;
    canSubmit: boolean;
    submitLabel?: string;
}>();

const emit = defineEmits<{
    (event: 'submit'): void;
    (event: 'cancel'): void;
    (event: 'add-step'): void;
    (event: 'remove-step', index: number): void;
    (event: 'add-ingredient'): void;
    (event: 'remove-ingredient', index: number): void;
}>();
</script>

<template>
    <form class="grid gap-3" @submit.prevent="emit('submit')">
        <div class="grid gap-3 md:grid-cols-2">
            <div class="md:col-span-2">
                <Label for="recipe-title">Title</Label>
                <Input id="recipe-title" v-model="props.form.title" placeholder="e.g. Roast Potatoes" />
            </div>

            <div class="md:col-span-2">
                <Label for="recipe-description">Description</Label>
                <textarea
                    id="recipe-description"
                    v-model="props.form.description"
                    rows="3"
                    class="w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm"
                    placeholder="Short description"
                />
            </div>

            <div>
                <Label for="recipe-prep-time">Prep (mins)</Label>
                <Input id="recipe-prep-time" v-model="props.form.prepTimeMinutes" type="number" min="0" />
            </div>

            <div>
                <Label for="recipe-cook-time">Cook (mins)</Label>
                <Input id="recipe-cook-time" v-model="props.form.cookTimeMinutes" type="number" min="0" />
            </div>

            <div>
                <Label for="recipe-servings">Servings</Label>
                <Input id="recipe-servings" v-model="props.form.servings" type="number" min="1" />
            </div>

            <div>
                <Label for="recipe-source-type">Source Type</Label>
                <select
                    id="recipe-source-type"
                    v-model="props.form.sourceType"
                    class="w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm"
                >
                    <option value="manual">Manual</option>
                    <option value="site_import">Site import</option>
                    <option value="ai_generated">AI generated</option>
                </select>
            </div>

            <div>
                <Label for="recipe-source-url">Source URL</Label>
                <Input id="recipe-source-url" v-model="props.form.sourceUrl" placeholder="https://example.com/recipe" />
            </div>

            <div class="md:col-span-2">
                <Label for="recipe-tags">Tags</Label>
                <Input id="recipe-tags" v-model="props.form.tagsRaw" placeholder="e.g. quick, dinner, vegetarian" />
            </div>

            <div class="md:col-span-2">
                <Label for="recipe-nutrition">Nutrition JSON</Label>
                <textarea
                    id="recipe-nutrition"
                    v-model="props.form.nutritionRaw"
                    rows="5"
                    class="w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm font-mono"
                />
            </div>
        </div>

        <section class="rounded-xl border border-emerald-900/12 bg-emerald-50/40 p-3">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-emerald-900">Steps</h3>
                <button type="button" class="rounded-lg border border-emerald-900/20 px-2 py-1 text-xs font-semibold" @click="emit('add-step')">Add step</button>
            </div>

            <div class="grid gap-2">
                <div v-for="(step, idx) in props.form.steps" :key="idx" class="grid gap-2 md:grid-cols-[1fr_auto]">
                    <Input v-model="step.instruction" placeholder="Describe this step" />
                    <button type="button" class="rounded-lg border border-rose-700/25 px-2 py-1 text-xs font-semibold text-rose-700" @click="emit('remove-step', idx)">Remove</button>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-emerald-900/12 bg-emerald-50/40 p-3">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-emerald-900">Ingredients</h3>
                <button type="button" class="rounded-lg border border-emerald-900/20 px-2 py-1 text-xs font-semibold" @click="emit('add-ingredient')">Add ingredient</button>
            </div>

            <div class="grid gap-2">
                <div v-for="(ingredient, idx) in props.form.ingredients" :key="idx" class="grid gap-2 rounded-lg border border-emerald-900/10 bg-white/75 p-2">
                    <Input v-model="ingredient.ingredient_text" placeholder="Ingredient name" />
                    <div class="grid gap-2 sm:grid-cols-2">
                        <Input v-model="ingredient.amount" type="number" step="any" min="0" placeholder="Amount" />
                        <Input v-model="ingredient.unit" placeholder="Unit" />
                    </div>
                    <Input v-model="ingredient.preparation_note" placeholder="Preparation note (optional)" />
                    <label class="inline-flex items-center gap-2 text-xs text-emerald-900">
                        <input v-model="ingredient.is_optional" type="checkbox" />
                        Optional ingredient
                    </label>
                    <button type="button" class="rounded-lg border border-rose-700/25 px-2 py-1 text-xs font-semibold text-rose-700" @click="emit('remove-ingredient', idx)">Remove ingredient</button>
                </div>
            </div>
        </section>

        <p v-if="props.error" class="text-sm text-rose-700">{{ props.error }}</p>

        <div class="flex items-center justify-end gap-2">
            <button type="button" class="rounded-xl border border-emerald-900/20 px-3 py-2 text-sm font-semibold" @click="emit('cancel')">Cancel</button>
            <Button type="submit" :disabled="!props.canSubmit">
                {{ props.loading ? 'Saving...' : (props.submitLabel || 'Save Recipe') }}
            </Button>
        </div>
    </form>
</template>
