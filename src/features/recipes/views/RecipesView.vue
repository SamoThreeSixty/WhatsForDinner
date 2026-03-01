<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref} from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import Input from '@/components/ui/Input.vue';
import RecipeForm from '@/features/recipes/components/RecipeForm.vue';
import {useRecipes} from '@/features/recipes/composable/useRecipes.ts';
import {useRecipeEditor} from '@/features/recipes/composable/useRecipeEditor.ts';
import type {Recipe} from '@/features/recipes/types/recipe.ts';

const recipes = useRecipes();
const editor = useRecipeEditor();
const isFormOpen = ref<boolean>(false);

onMounted(async () => {
    await recipes.loadRecipes();
});

onBeforeUnmount(() => {
    recipes.clearTimer();
});

function openCreate() {
    editor.resetForm();
    isFormOpen.value = true;
}

function openEdit(recipe: Recipe) {
    editor.loadRecipe(recipe);
    isFormOpen.value = true;
}

async function submitForm() {
    const saved = await editor.submit();
    if (!saved) {
        return;
    }

    await recipes.loadRecipes();
    isFormOpen.value = false;
}

function closeForm() {
    isFormOpen.value = false;
    editor.resetForm();
}

async function removeRecipe(recipe: Recipe) {
    const confirmed = window.confirm(`Delete "${recipe.title}"?`);
    if (!confirmed) {
        return;
    }

    await recipes.removeRecipe(recipe.id);
}
</script>

<template>
    <section class="space-y-4">
        <PageHeader
            eyebrow="Recipes"
            title="Recipe Library"
            subtitle="Add and manage household recipes with ingredients, steps, tags, and nutrition data."
        />

        <section class="rounded-2xl border border-emerald-800/10 bg-white/78 p-4 shadow-[0_10px_22px_rgba(8,72,43,0.1)] md:p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xs font-semibold uppercase tracking-[0.11em] text-emerald-800/85">All recipes</h2>
                <div class="flex items-center gap-2">
                    <button type="button" class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold" @click="openCreate">
                        Add
                    </button>
                    <button type="button" class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold" :disabled="recipes.loading.value" @click="recipes.loadRecipes()">
                        {{ recipes.loading.value ? 'Refreshing...' : 'Refresh' }}
                    </button>
                </div>
            </div>

            <Input
                :value="recipes.filters.value.q"
                type="text"
                placeholder="Type to search recipes..."
                class="mt-3 w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm"
                @input="(event) => recipes.onSearchInput((event.target as HTMLInputElement).value)"
            />

            <div class="mt-3 grid gap-2 md:grid-cols-4">
                <Input
                    :value="recipes.filters.value.tag"
                    type="text"
                    placeholder="Filter by tag"
                    @input="(event) => recipes.filters.value.tag = (event.target as HTMLInputElement).value"
                />

                <Input
                    :value="recipes.filters.value.max_cook_time ?? ''"
                    type="number"
                    min="1"
                    placeholder="Max cook mins"
                    @input="(event) => {
                        const value = (event.target as HTMLInputElement).value;
                        recipes.filters.value.max_cook_time = value === '' ? null : Number(value);
                    }"
                />

                <select
                    v-model="recipes.filters.value.source_type"
                    class="w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm"
                >
                    <option value="">All sources</option>
                    <option value="manual">Manual</option>
                    <option value="site_import">Site import</option>
                    <option value="ai_generated">AI generated</option>
                </select>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold"
                        :disabled="recipes.loading.value"
                        @click="recipes.loadRecipes()"
                    >
                        Apply
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold"
                        @click="() => { recipes.resetFilters(); recipes.loadRecipes(); }"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <p v-if="recipes.error.value" class="mt-2 text-sm text-rose-700">{{ recipes.error.value }}</p>

            <p v-if="!recipes.loading.value && recipes.items.value.length === 0" class="mt-3 text-sm text-(--green-muted)">
                No recipes yet.
            </p>

            <ul v-else class="mt-3 space-y-2">
                <li v-for="item in recipes.items.value" :key="item.id" class="rounded-xl border border-emerald-900/10 bg-white/80 p-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">{{ item.title }}</p>
                            <p class="text-xs text-emerald-800/75">
                                {{ item.cook_time_minutes ?? 0 }} min cook · {{ item.servings ?? '-' }} servings
                            </p>
                        </div>
                        <button type="button" class="rounded-lg border border-emerald-900/20 px-2.5 py-1 text-xs font-semibold" @click="openEdit(item)">
                            Edit
                        </button>
                        <button type="button" class="rounded-lg border border-rose-700/25 px-2.5 py-1 text-xs font-semibold text-rose-700" @click="removeRecipe(item)">
                            Delete
                        </button>
                    </div>
                </li>
            </ul>
        </section>

        <section
            v-if="isFormOpen"
            class="rounded-2xl border border-emerald-900/12 bg-white/84 p-4 shadow-[0_10px_22px_rgba(8,72,43,0.1)] md:p-5"
        >
            <h2 class="mb-3 text-sm font-semibold text-emerald-900">
                {{ editor.currentRecipeId.value ? 'Edit Recipe' : 'Create Recipe' }}
            </h2>

            <RecipeForm
                :form="editor.form.value"
                :loading="editor.loading.value"
                :error="editor.error.value"
                :can-submit="editor.canSubmit.value"
                @submit="submitForm"
                @cancel="closeForm"
                @add-step="editor.addStep"
                @remove-step="editor.removeStep"
                @add-ingredient="editor.addIngredient"
                @remove-ingredient="editor.removeIngredient"
            />
        </section>
    </section>
</template>
