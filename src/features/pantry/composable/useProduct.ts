import {computed, ref} from "vue";
import {type Product, type ProductOption, toProductOptions} from "@/features/pantry/types/product.ts";
import {listProducts} from "@/features/pantry/services/product.service.ts";

export function useProduct() {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const selectedIngredient = ref<number | null>(null);
    const productSearchTimer = ref<number | null>(null);
    const selectedProduct = ref<string>('');
    const query = ref<string>('');
    const products = ref<Product[]>([]);
    const productOptions = ref<ProductOption[]>([]);

    const selectedProductRecord = computed<Product | null>(() => {
        const selectedId = Number(selectedProduct.value);
        if (!Number.isInteger(selectedId) || selectedId <= 0) {
            return null;
        }

        return products.value.find((product) => product.id === selectedId) ?? null;
    });

    async function loadProductOptions() {
        const parsedIngredientId = Number(selectedIngredient.value);
        if (!Number.isInteger(parsedIngredientId) || parsedIngredientId <= 0) {
            products.value = [];
            productOptions.value = [];
            return;
        }

        loading.value = true;
        error.value = null;

        try {
            const items = await listProducts(parsedIngredientId, query.value, 20);
            products.value = items;
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
        selectedProductRecord,
        products,
        productOptions,
        loadProductOptions,
        onProductSearchInput,

        clearTimer
    };
}
