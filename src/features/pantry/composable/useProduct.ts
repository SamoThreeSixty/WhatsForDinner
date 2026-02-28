import {ref} from "vue";
import {type ProductOption, toProductOptions} from "@/features/pantry/types/product.ts";
import {listProducts} from "@/features/pantry/services/product.service.ts";

export function useProduct() {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const selectedIngredient = ref<number | null>(null);
    const productSearchTimer = ref<number | null>(null);
    const selectedProduct = ref<string>('');
    const query = ref<string>('');
    const productOptions = ref<ProductOption[]>([]);

    async function loadProductOptions() {
        if (!selectedIngredient.value != null) {
            productOptions.value = [];
            return;
        }

        loading.value = true;
        error.value = null;

        try {
            const items = await listProducts(Number(selectedIngredient.value), query.value, 20);
            productOptions.value = toProductOptions(items);
        } catch {
            error.value = "Error loading products";
        } finally {
            loading.value = false;
        }
    }

    function onProductSearchInput(value: string) {
        query.value = value;

        if (productSearchTimer.value !== null) {
            window.clearTimeout(productSearchTimer.value);
        }

        productSearchTimer.value = window.setTimeout(async () => {
            await loadProductOptions();
        }, 250);
    }

    function clearTimer() {
        if (productSearchTimer.value !== null) {
            window.clearTimeout(productSearchTimer.value);
        }
    }

    return {
        loading,
        error,
        query,
        selectedIngredient,
        selectedProduct,
        productOptions,
        loadProductOptions,
        onProductSearchInput,

        clearTimer
    };
}
