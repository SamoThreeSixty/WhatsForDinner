import {computed, ref} from "vue";
import type {Product} from "@/features/pantry/types/product.ts";
import type {CreateInventoryPayload, InventoryItem} from "@/features/pantry/types/inventry.ts";
import {createInventoryItem, getInventoryMetadata} from "@/features/pantry/services/inventory.service.ts";

interface InventoryAddForm {
    quantity: string;
    unit: string;
    purchasedAt: string;
    expiresAt: string;
    location: string;
}

function parseDefaultUnit(value?: string | null): {quantity: string; unit: string} {
    if (!value) {
        return {quantity: '', unit: ''};
    }

    const normalized = value.trim();
    const match = normalized.match(/^([0-9]+(?:\.[0-9]+)?)\s*([a-zA-Z]+)$/);
    if (!match) {
        return {quantity: '', unit: normalized};
    }

    return {
        quantity: match[1] ?? '',
        unit: match[2] ?? '',
    };
}

export function useInventoryAdd(emit: (key: string, item: unknown) => void) {
    const loading = ref<boolean>(false);
    const metadataLoading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const selectedProduct = ref<Product | null>(null);
    const locationOptions = ref<string[]>([]);
    const addingLocation = ref<boolean>(false);
    const locationDraft = ref<string>('');
    const form = ref<InventoryAddForm>({
        quantity: '',
        unit: '',
        purchasedAt: '',
        expiresAt: '',
        location: '',
    });

    const unitOptions = computed<string[]>(() => selectedProduct.value?.unit_options ?? []);

    function resetForm() {
        form.value = {
            quantity: '',
            unit: '',
            purchasedAt: '',
            expiresAt: '',
            location: '',
        };
        addingLocation.value = false;
        locationDraft.value = '';
        error.value = null;
    }

    async function loadMetadata() {
        metadataLoading.value = true;

        try {
            const metadata = await getInventoryMetadata();
            locationOptions.value = metadata.locations ?? [];
        } catch {
            locationOptions.value = [];
        } finally {
            metadataLoading.value = false;
        }
    }

    async function onOpen() {
        resetForm();
        await loadMetadata();
    }

    function setProduct(product: Product | null) {
        selectedProduct.value = product;

        if (!product) {
            form.value.quantity = '';
            form.value.unit = '';
            return;
        }

        const parsed = parseDefaultUnit(product.unit_default);
        form.value.quantity = parsed.quantity;
        form.value.unit = parsed.unit || (product.unit_options?.[0] ?? '');
    }

    function validate(): boolean {
        if (loading.value) {
            return false;
        }

        if (!selectedProduct.value) {
            error.value = "Select a product first.";
            return false;
        }

        const quantity = Number(form.value.quantity);
        if (!Number.isFinite(quantity) || quantity <= 0) {
            error.value = "Enter a valid quantity.";
            return false;
        }

        if (!form.value.unit.trim()) {
            error.value = "Unit is required.";
            return false;
        }

        if (form.value.purchasedAt && form.value.expiresAt && form.value.expiresAt < form.value.purchasedAt) {
            error.value = "Expiry date must be on or after purchased date.";
            return false;
        }

        return true;
    }

    async function submit() {
        if (!validate()) {
            return;
        }

        loading.value = true;
        error.value = null;

        const payload: CreateInventoryPayload = {
            product_id: Number(selectedProduct.value!.id),
            quantity: Number(form.value.quantity),
            unit: form.value.unit.trim(),
            purchased_at: form.value.purchasedAt || null,
            expires_at: form.value.expiresAt || null,
            location: form.value.location.trim() || null,
        };

        try {
            const created: InventoryItem = await createInventoryItem(payload);
            emit('created', created);
            resetForm();
            await loadMetadata();
        } catch {
            error.value = "Failed to add inventory item.";
        } finally {
            loading.value = false;
        }
    }

    function startAddLocation() {
        addingLocation.value = true;
        locationDraft.value = '';
    }

    function cancelAddLocation() {
        addingLocation.value = false;
        locationDraft.value = '';
    }

    function addLocationOption() {
        const value = locationDraft.value.trim();
        if (!value) {
            return;
        }

        if (!locationOptions.value.includes(value)) {
            locationOptions.value = [...locationOptions.value, value].sort((a, b) => a.localeCompare(b));
        }

        form.value.location = value;
        cancelAddLocation();
    }

    return {
        loading,
        metadataLoading,
        error,
        selectedProduct,
        locationOptions,
        addingLocation,
        locationDraft,
        unitOptions,
        form,
        onOpen,
        setProduct,
        startAddLocation,
        cancelAddLocation,
        addLocationOption,
        submit,
    };
}
