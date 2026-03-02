import {ref} from "vue";
import {deleteRecipe, listRecipes} from "@/features/recipes/services/recipe.service.ts";
import type {Recipe, RecipeListPagination, RecipeListParams, RecipeSourceType} from "@/features/recipes/types/recipe.ts";
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
        per_page: limit,
    };
}

export function useRecipes() {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);
    const items = ref<Recipe[]>([]);
    const pagination = ref<RecipeListPagination>({
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
    });
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

    async function loadRecipes(page = 1, perPage = pagination.value.per_page) {
        loading.value = true;
        error.value = null;

        try {
            const result = await listRecipes({
                ...toListParams(filters.value, perPage),
                page,
            });

            items.value = result.data;
            pagination.value = result.pagination;
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
            pagination.value.total = Math.max(0, pagination.value.total - 1);

            if (items.value.length === 0 && pagination.value.current_page > 1) {
                await loadRecipes(pagination.value.current_page - 1);
                return;
            }
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
            await loadRecipes(1);
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

    async function goToPage(page: number) {
        if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) {
            return;
        }

        await loadRecipes(page);
    }

    return {
        loading,
        error,
        items,
        pagination,
        tagLoading,
        tagOptions,
        filters,
        loadRecipes,
        goToPage,
        removeRecipe,
        upsertRecipe,
        loadTagOptions,
        onTagSearchInput,
        onSearchInput,
        clearTimer,
        resetFilters,
    };
}
