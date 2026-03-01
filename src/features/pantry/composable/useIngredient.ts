import {ref} from "vue";
import {type IngredientOption, toIngredientOptions} from "@/features/pantry/types/ingredient.ts";
import {listIngredients} from "@/features/pantry/services/ingredient.service.ts";


export function useIngredient() {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const ingredientSearchTimer = ref<number | null>(null);
    const selectedIngredient = ref<number | null>(null);
    const query = ref<string>('');
    const ingredientOptions = ref<IngredientOption[]>([])

    async function loadIngredientOptions() {
        loading.value = true
        error.value = null

        try {
            const items = await listIngredients(query.value, 20);
            ingredientOptions.value = toIngredientOptions(items);
        } catch {
            error.value = "Error loading ingredients"
        } finally {
            loading.value = false;
        }
    }

    async function onIngredientSearchInput(value: string) {
        query.value = value;

        if (ingredientSearchTimer.value !== null) {
            window.clearTimeout(ingredientSearchTimer.value);
        }

        ingredientSearchTimer.value = window.setTimeout(async () => {
            await loadIngredientOptions();
        }, 250);
    }

    function clearTimer() {
        if (ingredientSearchTimer.value !== null) {
            window.clearTimeout(ingredientSearchTimer.value);
        }

    }

    return {
        loading,
        error,
        query,
        selectedIngredient,
        loadIngredientOptions,
        onIngredientSearchInput,
        ingredientOptions,

        clearTimer
    }
}
