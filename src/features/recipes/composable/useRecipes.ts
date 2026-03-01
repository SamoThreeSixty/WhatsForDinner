import {ref} from "vue";
import {listRecipes} from "@/features/recipes/services/recipe.service.ts";
import type {Recipe, RecipeListParams, RecipeSourceType} from "@/features/recipes/types/recipe.ts";

interface RecipeFilters {
    q: string;
    tag: string;
    ingredient_id: number | null;
    ingredient_slug: string;
    max_cook_time: number | null;
    source_type: RecipeSourceType | '';
}

function toListParams(filters: RecipeFilters, limit = 50): RecipeListParams {
    return {
        q: filters.q.trim() || undefined,
        tag: filters.tag.trim() || undefined,
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

    const filters = ref<RecipeFilters>({
        q: '',
        tag: '',
        ingredient_id: null,
        ingredient_slug: '',
        max_cook_time: null,
        source_type: '',
    });

    const searchTimer = ref<number | null>(null);

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
    }

    function resetFilters() {
        filters.value = {
            q: '',
            tag: '',
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
        filters,
        loadRecipes,
        onSearchInput,
        clearTimer,
        resetFilters,
    };
}
