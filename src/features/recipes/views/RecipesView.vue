<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref} from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import Input from '@/components/ui/Input.vue';
import MultiSelect from '@/components/ui/MultiSelect.vue';
import PaginationControls from '@/components/ui/PaginationControls.vue';
import RecipeEditorModal from '@/features/recipes/components/RecipeEditorModal.vue';
import {useRecipes} from '@/features/recipes/composable/useRecipes.ts';
import type {Recipe} from '@/features/recipes/types/recipe.ts';

const recipes = useRecipes();
const isFormOpen = ref<boolean>(false);
const editingRecipe = ref<Recipe | null>(null);
const isFiltersOpen = ref<boolean>(false);
const draftFilters = ref({
    tags: [] as string[],
    max_cook_time_input: '',
    source_type: '' as '' | 'manual' | 'site_import' | 'ai_generated',
});

onMounted(async () => {
    await recipes.loadRecipes();
});

onBeforeUnmount(() => {
    recipes.clearTimer();
});

function openCreate() {
    editingRecipe.value = null;
    isFormOpen.value = true;
}

function openEdit(recipe: Recipe) {
    editingRecipe.value = recipe;
    isFormOpen.value = true;
}

function onRecipeSaved(recipe: Recipe) {
    recipes.upsertRecipe(recipe);
}

function closeForm() {
    isFormOpen.value = false;
    editingRecipe.value = null;
}

async function removeRecipe(recipe: Recipe) {
    const confirmed = window.confirm(`Delete "${recipe.title}"?`);
    if (!confirmed) {
        return;
    }

    await recipes.removeRecipe(recipe.id);
}

function openFilters() {
    draftFilters.value = {
        tags: [...recipes.filters.value.tags],
        max_cook_time_input: recipes.filters.value.max_cook_time?.toString() ?? '',
        source_type: recipes.filters.value.source_type,
    };

    void recipes.loadTagOptions();
    isFiltersOpen.value = true;
}

async function applyFilters() {
    const maxCook = draftFilters.value.max_cook_time_input.trim() === ''
        ? null
        : Number(draftFilters.value.max_cook_time_input);

    recipes.filters.value.tags = [...draftFilters.value.tags];
    recipes.filters.value.max_cook_time = Number.isFinite(maxCook) && maxCook !== null && maxCook > 0
        ? Math.floor(maxCook)
        : null;
    recipes.filters.value.source_type = draftFilters.value.source_type;
    await recipes.loadRecipes(1);
    isFiltersOpen.value = false;
}

async function resetFilters() {
    recipes.resetFilters();
    draftFilters.value = {
        tags: [],
        max_cook_time_input: '',
        source_type: '',
    };
    await recipes.loadRecipes(1);
    isFiltersOpen.value = false;
}

function onMaxCookInput(value: string) {
    draftFilters.value.max_cook_time_input = value.replace(/[^\d]/g, '');
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
                    <button type="button" class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold" @click="isFiltersOpen ? (isFiltersOpen = false) : openFilters()">
                        {{ isFiltersOpen ? 'Hide filters' : 'Filters' }}
                    </button>
                    <button type="button" class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold" :disabled="recipes.loading.value" @click="recipes.loadRecipes(recipes.pagination.value.current_page)">
                        {{ recipes.loading.value ? 'Refreshing...' : 'Refresh' }}
                    </button>
                </div>
            </div>

            <Input
                :model-value="recipes.filters.value.q"
                type="text"
                placeholder="Type to search recipes..."
                class="mt-3 w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm"
                @update:model-value="recipes.onSearchInput"
            />

            <div v-if="isFiltersOpen" class="mt-3 flex flex-wrap items-start gap-2 rounded-xl border border-emerald-900/12 bg-emerald-50/35 p-3">
                <div class="min-w-[240px] flex-1">
                    <MultiSelect
                        :model-value="draftFilters.tags"
                        :options="recipes.tagOptions.value"
                        :loading="recipes.tagLoading.value"
                        placeholder="Filter by tags"
                        no-results-text="No tags found"
                        @update:model-value="(value) => draftFilters.tags = value"
                        @search="recipes.onTagSearchInput"
                    />
                </div>

                <div class="w-full sm:w-[170px]">
                    <Input
                        :model-value="draftFilters.max_cook_time_input"
                        type="text"
                        inputmode="numeric"
                        min="1"
                        placeholder="Max cook mins"
                        @update:model-value="onMaxCookInput"
                    />
                </div>

                <div class="w-full sm:w-[180px]">
                    <select
                        v-model="draftFilters.source_type"
                        class="w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm"
                    >
                        <option value="">All sources</option>
                        <option value="manual">Manual</option>
                        <option value="site_import">Site import</option>
                        <option value="ai_generated">AI generated</option>
                    </select>
                </div>

                <div class="flex w-full items-center gap-2 sm:w-auto">
                    <button
                        type="button"
                        class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold"
                        :disabled="recipes.loading.value"
                        @click="applyFilters"
                    >
                        Apply
                    </button>
                    <button
                        type="button"
                        class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold"
                        @click="resetFilters"
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
                            <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                <span class="rounded-full border border-emerald-900/18 bg-emerald-50 px-2 py-0.5 text-[0.66rem] font-semibold uppercase tracking-[0.04em] text-emerald-900">
                                    {{ item.source_type.replace('_', ' ') }}
                                </span>
                                <span
                                    v-for="tag in item.tags ?? []"
                                    :key="tag.id"
                                    class="rounded-full border border-emerald-900/12 bg-white px-2 py-0.5 text-[0.66rem] font-medium text-emerald-800"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" class="rounded-lg border border-emerald-900/20 px-2.5 py-1 text-xs font-semibold" @click="openEdit(item)">
                                Edit
                            </button>
                            <button type="button" class="rounded-lg border border-rose-700/25 px-2.5 py-1 text-xs font-semibold text-rose-700" @click="removeRecipe(item)">
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            </ul>

            <PaginationControls
                :current-page="recipes.pagination.value.current_page"
                :last-page="recipes.pagination.value.last_page"
                :total="recipes.pagination.value.total"
                :loading="recipes.loading.value"
                @change="recipes.goToPage"
            />
        </section>

        <RecipeEditorModal
            :model-value="isFormOpen"
            :recipe="editingRecipe"
            @update:model-value="(value) => { if (!value) closeForm(); }"
            @saved="onRecipeSaved"
        />
    </section>
</template>
