import {computed, ref} from "vue";
import type {
    AddProduct,
    CreateProductPayload,
    ProductMetadataResponse,
    UnitTypeOption
} from "@/features/pantry/types/product.ts";
import {createProduct, getProductMetadata} from "@/features/pantry/services/product.service.ts";

export function useProductAdd(emit: (key: string, item: any) => void) {
    const loading = ref<boolean>(false);
    const metadataLoading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const ingredientId = ref<number | null>(null);
    const unitTypeOptions = ref<UnitTypeOption[]>([]);
    const unitsByType = ref<Record<string, string[]>>({});

    const form = ref<AddProduct>({
        company: '',
        name: '',
        unitType: null,
        unitAmount: '',
        unitValue: '',
    });

    const unitValueOptions = computed<string[]>(() => {
        const selectedType = form.value.unitType;
        if (!selectedType) {
            return [];
        }

        return unitsByType.value[selectedType] ?? [];
    });

    function resetForm() {
        form.value = {
            company: '',
            name: '',
            unitType: null,
            unitAmount: '',
            unitValue: '',
        };
        error.value = null;
    }

    async function loadMetadata() {
        metadataLoading.value = true;
        error.value = null;

        try {
            const metadata: ProductMetadataResponse = await getProductMetadata();
            unitTypeOptions.value = metadata.unit_types ?? [];
            unitsByType.value = metadata.units_by_type ?? {};

            const _firstType = unitTypeOptions.value[0];
            if (!form.value.unitType && _firstType !== undefined) {
                form.value.unitType = _firstType.value;
            }
        } catch {
            error.value = "Failed to load product units.";
        } finally {
            metadataLoading.value = false;
        }
    }

    async function onOpen(selectedIngredientId: number | string) {
        ingredientId.value = Number(selectedIngredientId);
        resetForm();
        await loadMetadata();
    }

    function onUnitTypeChange(value: string) {
        form.value.unitType = value as AddProduct["unitType"];
        form.value.unitValue = '';
    }

    function validate() {
        if (loading.value) {
            return false;
        }

        if (!ingredientId.value || ingredientId.value <= 0) {
            error.value = "Select an ingredient first.";
            return false;
        }

        if (form.value.name.trim() === "") {
            error.value = "Product name is required.";
            return false;
        }

        if (!form.value.unitType) {
            error.value = "Unit type is required.";
            return false;
        }

        const parsedAmount = Number(form.value.unitAmount);
        if (!Number.isFinite(parsedAmount) || parsedAmount <= 0) {
            error.value = "Enter a valid amount.";
            return false;
        }

        if (!form.value.unitValue) {
            error.value = "Select a unit value.";
            return false;
        }

        return true;
    }

    async function submit() {
        if (!validate()) {
            return;
        }

        loading.value = true;

        const payload: CreateProductPayload = {
            ingredient_id: Number(ingredientId.value),
            company: form.value.company.trim() || null,
            name: form.value.name.trim(),
            unit_type: form.value.unitType!,
            unit_options: unitValueOptions.value,
            unit_default: `${form.value.unitAmount.trim()} ${form.value.unitValue}`.trim(),
        };

        try {
            const created = await createProduct(payload);
            emit("created", created);
            emit("update:modelValue", false);

            resetForm();
        } catch {
            error.value = "Failed to create product.";
        } finally {
            loading.value = false;
        }
    }

    return {
        loading,
        metadataLoading,
        error,
        ingredientId,
        unitTypeOptions,
        unitValueOptions,
        form,
        onOpen,
        onUnitTypeChange,
        submit
    }
}
