import {ref} from "vue";
import {deleteRecipe, listRecipes} from "@/features/recipes/services/recipe.service.ts";
import type {Recipe, RecipeListParams, RecipeSourceType} from "@/features/recipes/types/recipe.ts";
import {getApiErrorMessage} from "@/lib/errors.ts";
import {listTags} from "@/features/recipes/services/tag.service.ts";
import {toTagOptions, type TagOption} from "@/features/recipes/types/tag.ts";

interface RecipeFilters {
    q: string;
    tags: string[];
    ingredient_id: number | null;
    ingredient_slug: string;
    max_cook_time: number | null;
    source_type: RecipeSourceType | '';
}

function toListParams(filters: RecipeFilters, limit = 50): RecipeListParams {
    return {
        q: filters.q.trim() || undefined,
        tags: filters.tags.length > 0 ? filters.tags : undefined,
        ingredient_id: filters.ingredient_id ?? undefined,
        ingredient_slug: filters.ingredient_slug.trim() || undefined,
        max_cook_time: filters.max_cook_time ?? undefined,
        source_type: filters.source_type || undefined,
        limit,
    };
}

export function useRecipes() {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);
    const items = ref<Recipe[]>([]);
    const tagLoading = ref<boolean>(false);
    const tagOptions = ref<TagOption[]>([]);

    const filters = ref<RecipeFilters>({
        q: '',
        tags: [],
        ingredient_id: null,
        ingredient_slug: '',
        max_cook_time: null,
        source_type: '',
    });

    const searchTimer = ref<number | null>(null);
    const tagSearchTimer = ref<number | null>(null);

    async function loadRecipes(limit = 50) {
        loading.value = true;
        error.value = null;

        try {
            items.value = await listRecipes(toListParams(filters.value, limit));
        } catch {
            error.value = "Failed to load recipes.";
        } finally {
            loading.value = false;
        }
    }

    async function removeRecipe(recipeId: number) {
        loading.value = true;
        error.value = null;

        try {
            await deleteRecipe(recipeId);
            items.value = items.value.filter((item) => item.id !== recipeId);
        } catch (e) {
            error.value = getApiErrorMessage(e, "Failed to delete recipe.");
        } finally {
            loading.value = false;
        }
    }

    function upsertRecipe(recipe: Recipe) {
        const existingIndex = items.value.findIndex((item) => item.id === recipe.id);
        if (existingIndex >= 0) {
            items.value[existingIndex] = recipe;
            return;
        }

        items.value.unshift(recipe);
    }

    function onSearchInput(value: string) {
        filters.value.q = value;

        if (searchTimer.value !== null) {
            window.clearTimeout(searchTimer.value);
        }

        searchTimer.value = window.setTimeout(async () => {
            await loadRecipes();
        }, 250);
    }

    function clearTimer() {
        if (searchTimer.value !== null) {
            window.clearTimeout(searchTimer.value);
        }

        if (tagSearchTimer.value !== null) {
            window.clearTimeout(tagSearchTimer.value);
        }
    }

    async function loadTagOptions(search = '') {
        tagLoading.value = true;

        try {
            const tags = await listTags(search, 50);
            tagOptions.value = toTagOptions(tags);
        } catch {
            tagOptions.value = [];
        } finally {
            tagLoading.value = false;
        }
    }

    function onTagSearchInput(value: string) {
        if (tagSearchTimer.value !== null) {
            window.clearTimeout(tagSearchTimer.value);
        }

        tagSearchTimer.value = window.setTimeout(async () => {
            await loadTagOptions(value);
        }, 250);
    }

    function resetFilters() {
        filters.value = {
            q: '',
            tags: [],
            ingredient_id: null,
            ingredient_slug: '',
            max_cook_time: null,
            source_type: '',
        };
    }

    return {
        loading,
        error,
        items,
        tagLoading,
        tagOptions,
        filters,
        loadRecipes,
        removeRecipe,
        upsertRecipe,
        loadTagOptions,
        onTagSearchInput,
        onSearchInput,
        clearTimer,
        resetFilters,
    };
}
