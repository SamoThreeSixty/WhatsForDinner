<script setup lang="ts">
import {onBeforeUnmount, ref, watch} from 'vue';
import DialogModal from '@/components/ui/DialogModal.vue';
import SearchSelection from '@/components/ui/SearchSelection.vue';
import Input from '@/components/ui/Input.vue';
import Select from '@/components/ui/Select.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';
import ProductCreateModal from '@/features/pantry/components/ProductCreateModal.vue';
import {useInventoryAdd} from '@/features/pantry/composable/useInventoryAdd.ts';
import {useIngredient} from '@/features/pantry/composable/useIngredient.ts';
import {useProduct} from '@/features/pantry/composable/useProduct.ts';
import type {InventoryItem} from '@/features/pantry/types/inventry.ts';
import type {Product} from '@/features/pantry/types/product.ts';

const props = defineProps<{
    modelValue: boolean;
}>();

const emit = defineEmits<{
    (event: 'update:modelValue', value: boolean): void;
    (event: 'created', value: InventoryItem): void;
}>();

const ingredient = useIngredient();
const product = useProduct();
const isProductModalOpen = ref<boolean>(false);

const emitInventoryAdd = (key: string, item: unknown) => {
    if (key === 'created') {
        emit('created', item as InventoryItem);
        emit('update:modelValue', false);
    }
};

const inventoryAdd = useInventoryAdd(emitInventoryAdd);

watch(() => props.modelValue, async (isOpen) => {
    if (!isOpen) {
        return;
    }

    ingredient.query.value = '';
    ingredient.selectedIngredient.value = null;

    product.query.value = '';
    product.selectedIngredient.value = null;
    product.selectedProduct.value = '';

    await Promise.all([
        ingredient.loadIngredientOptions(),
        inventoryAdd.onOpen(),
    ]);
});

watch(ingredient.selectedIngredient, async (ingredientId) => {
    product.selectedProduct.value = '';
    product.query.value = '';
    product.selectedIngredient.value = ingredientId;
    await product.loadProductOptions();
});

watch(product.selectedProductRecord, (selectedProduct) => {
    inventoryAdd.setProduct(selectedProduct);
});

async function onProductCreated(createdProduct: Product) {
    await product.loadProductOptions();
    product.selectedProduct.value = String(createdProduct.id);
}

onBeforeUnmount(() => {
    ingredient.clearTimer();
    product.clearTimer();
});
</script>

<template>
    <DialogModal
        :model-value="modelValue"
        title="Add Inventory Item"
        @update:model-value="(value) => emit('update:modelValue', value)"
    >
        <form id="pantry-add-form" class="grid gap-2" @submit.prevent="inventoryAdd.submit">
            <SearchSelection
                v-model="ingredient.selectedIngredient.value"
                :options="ingredient.ingredientOptions.value"
                :loading="ingredient.loading.value"
                placeholder="Type ingredient name to search"
                no-results-text="No ingredients found"
                @search="ingredient.onIngredientSearchInput"
            />

            <div v-if="ingredient.selectedIngredient.value !== null" class="flex items-center gap-2">
                <SearchSelection
                    v-model="product.selectedProduct.value"
                    class="flex-1"
                    :options="product.productOptions.value"
                    :loading="product.loading.value"
                    placeholder="Type product name to search"
                    no-results-text="No products found"
                    @search="product.onProductSearchInput"
                />
                <Button type="button" class="shrink-0" @click="isProductModalOpen = true">Add</Button>
            </div>

            <template v-if="product.selectedProductRecord.value">
                <div>
                    <Label for="inventory-quantity">Quantity</Label>
                    <Input
                        id="inventory-quantity"
                        v-model="inventoryAdd.form.value.quantity"
                        type="number"
                        step="any"
                        min="0"
                        placeholder="e.g. 250"
                    />
                </div>

                <div>
                    <Label for="inventory-unit">Unit</Label>
                    <Select
                        v-if="inventoryAdd.unitOptions.value.length > 0"
                        id="inventory-unit"
                        v-model="inventoryAdd.form.value.unit"
                    >
                        <option value="" disabled>Select unit</option>
                        <option v-for="unit in inventoryAdd.unitOptions.value" :key="unit" :value="unit">{{ unit }}</option>
                    </Select>
                    <Input
                        v-else
                        id="inventory-unit"
                        v-model="inventoryAdd.form.value.unit"
                        placeholder="e.g. g, ml, each"
                    />
                </div>

                <div>
                    <Label for="inventory-purchased-at">Bought Date</Label>
                    <Input id="inventory-purchased-at" v-model="inventoryAdd.form.value.purchasedAt" type="date" />
                </div>

                <div>
                    <Label for="inventory-expires-at">Expiry Date</Label>
                    <Input id="inventory-expires-at" v-model="inventoryAdd.form.value.expiresAt" type="date" />
                </div>

                <div>
                    <Label for="inventory-location">Location</Label>
                    <div class="flex items-center gap-2">
                        <Select id="inventory-location" v-model="inventoryAdd.form.value.location" class="flex-1">
                            <option value="">No location</option>
                            <option
                                v-for="location in inventoryAdd.locationOptions.value"
                                :key="location"
                                :value="location"
                            >
                                {{ location }}
                            </option>
                        </Select>
                        <Button
                            v-if="!inventoryAdd.addingLocation.value"
                            type="button"
                            class="shrink-0"
                            @click="inventoryAdd.startAddLocation"
                        >
                            Add
                        </Button>
                    </div>
                </div>

                <div v-if="inventoryAdd.addingLocation.value">
                    <Label for="inventory-location-add">New Location</Label>
                    <div class="flex items-center gap-2">
                        <Input
                            id="inventory-location-add"
                            v-model="inventoryAdd.locationDraft.value"
                            class="flex-1"
                            placeholder="e.g. Outdoor fridge"
                        />
                        <Button type="button" class="shrink-0" @click="inventoryAdd.addLocationOption">Save</Button>
                        <button
                            type="button"
                            class="rounded-xl border border-emerald-900/20 px-3 py-2 text-sm font-semibold"
                            @click="inventoryAdd.cancelAddLocation"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <p v-if="inventoryAdd.error.value" class="text-sm text-rose-700">
                    {{ inventoryAdd.error.value }}
                </p>
            </template>
        </form>

        <template #footer>
            <button
                type="button"
                class="rounded-xl border border-emerald-900/20 px-3 py-2 text-sm font-semibold"
                @click="emit('update:modelValue', false)"
            >
                Cancel
            </button>
            <Button type="submit" form="pantry-add-form" :disabled="inventoryAdd.loading.value">
                {{ inventoryAdd.loading.value ? 'Adding...' : 'Add To Inventory' }}
            </Button>
        </template>
    </DialogModal>

    <ProductCreateModal
        v-if="ingredient.selectedIngredient.value"
        v-model="isProductModalOpen"
        :ingredient-id="ingredient.selectedIngredient.value"
        @created="onProductCreated"
    />
</template>
