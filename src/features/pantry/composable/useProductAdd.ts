import {ref} from "vue";
import type {AddProduct, CreateProductPayload} from "@/features/pantry/types/product.ts";
import {createProduct} from "@/features/pantry/services/product.service.ts";


export function useProductAdd(emit: (key: string, item: any) => void) {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const ingredientId = ref<number>();
    const form = ref<AddProduct>({
        company: '',
        name: '',
        // unit_type: 'count'
    });

    function validate() {
        if (loading.value) {
            return false;
        }

        if (!ingredientId) {
            error.value = "Select an ingredient first.";
            return false;
        }

        if (form.value.name.trim() === "") {
            error.value = "Product name is required.";
            return false;
        }

        // if (parsedUnitValues.value.length === 0) {
        //     error.value = "Add at least one unit value.";
        //     return;
        // }
        //
        // if (!parsedUnitValues.value.includes(unitDefault.value)) {
        //     error.value = "Select a valid default unit.";
        //     return;
        // }

        return true;
    }

    async function submit() {
        if (!validate()) {
            return;
        }

        loading.value = true;

        const payload: CreateProductPayload = {
            ingredient_id: Number(ingredientId.value),
            name: form.value.name.trim(),
            // unit_type: unitType.value,
            // unit_options: parsedUnitValues.value,
            // unit_default: unitDefault.value,
        };

        try {
            const created = await createProduct(payload);
            emit("created", created);
            emit("update:modelValue", false);
        } catch {
            error.value = "Failed to create product.";
        } finally {
            loading.value = false;
        }
    }

    return {
        loading,
        error,
        ingredientId,
        form,
        submit
    }
}
