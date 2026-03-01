<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref, watch} from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchSelection from '@/components/ui/SearchSelection.vue';
import PantryItem from "@/features/pantry/components/PantryItem.vue";
import Input from "@/components/ui/Input.vue";
import Button from "@/components/ui/Button.vue";
import Select from "@/components/ui/Select.vue";
import Label from "@/components/ui/Label.vue";
import ProductCreateModal from "@/features/pantry/components/ProductCreateModal.vue";
import {useInventoryList} from "@/features/pantry/composable/useInventry.ts";
import {useInventoryAdd} from "@/features/pantry/composable/useInventoryAdd.ts";
import {useIngredient} from "@/features/pantry/composable/useIngredient.ts";
import {useProduct} from "@/features/pantry/composable/useProduct.ts";
import type {InventoryItem} from "@/features/pantry/types/inventry.ts";
import type {Product} from "@/features/pantry/types/product.ts";

const inventory = useInventoryList();
onMounted(() => inventory.getInventoryItems());

const ingredient = useIngredient();
onMounted(() => ingredient.loadIngredientOptions());

const product = useProduct();
const isProductModalOpen = ref<boolean>(false);
const emitInventoryAdd = (key: string, item: unknown) => {
    if (key === 'created') {
        onInventoryItemCreated(item as InventoryItem);
    }
};
const inventoryAdd = useInventoryAdd(emitInventoryAdd);

onMounted(() => inventoryAdd.onOpen());

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

async function onInventoryItemCreated(createdItem: InventoryItem) {
    inventory.inventory.value.unshift(createdItem);
}

onBeforeUnmount(() => {
    ingredient.clearTimer();
    product.clearTimer();
    inventory.clearTimer();
});
</script>

<template>
    <section class="space-y-4">
        <PageHeader
            eyebrow="Larder"
            title="Ingredients"
            subtitle="See everything you have and manage your stock with quick add, edit, and remove actions."
        />

        <section
            class="rounded-2xl border border-emerald-800/10 bg-white/78 p-4 shadow-[0_10px_22px_rgba(8,72,43,0.1)] md:p-5">

            <form class="mt-3 grid gap-2 md:grid-cols-2">
                <SearchSelection
                    v-model="ingredient.selectedIngredient.value"
                    class="md:col-span-2"
                    :options="ingredient.ingredientOptions.value"
                    :loading="ingredient.loading.value"
                    placeholder="Type ingredient name to search"
                    no-results-text="No ingredients found"
                    @search="ingredient.onIngredientSearchInput"
                />

                <div v-if="ingredient.selectedIngredient.value !== null" class="md:col-span-2 flex items-center gap-2">
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

                    <div v-if="inventoryAdd.addingLocation.value" class="md:col-span-2">
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

                    <p v-if="inventoryAdd.error.value" class="md:col-span-2 text-sm text-rose-700">
                        {{ inventoryAdd.error.value }}
                    </p>

                    <div class="md:col-span-2 flex justify-end">
                        <Button
                            type="button"
                            :disabled="inventoryAdd.loading.value"
                            @click="inventoryAdd.submit"
                        >
                            {{ inventoryAdd.loading.value ? 'Adding...' : 'Add To Inventory' }}
                        </Button>
                    </div>
                </template>

            </form>

        </section>

        <ProductCreateModal
            v-if="ingredient.selectedIngredient.value"
            v-model="isProductModalOpen"
            :ingredient-id="ingredient.selectedIngredient.value"
            @created="onProductCreated"
        />

        <section
            class="rounded-2xl border border-emerald-800/10 bg-white/78 p-4 shadow-[0_10px_22px_rgba(8,72,43,0.1)] md:p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xs font-semibold uppercase tracking-[0.11em] text-emerald-800/85">All items</h2>
                <button type="button"
                        class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold"
                        :disabled="inventory.loading.value"
                        @click="inventory.getInventoryItems()"
                >
                    {{ inventory.loading.value ? 'Refreshing...' : 'Refresh' }}
                </button>
            </div>

            <Input
                :value="inventory.searchQuery.value"
                type="text"
                placeholder="Type to search ingredients..."
                class="mt-3 w-full rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm"
                @input="(event) => inventory.onSearchInput((event.target as HTMLInputElement).value)"
            />

            <p v-if="!inventory.loading.value && inventory.inventory.value.length === 0"
               class="mt-3 text-sm text-(--green-muted)">
                No inventory.
            </p>

            <ul v-else class="mt-3 space-y-2">
                <PantryItem v-for="item in inventory.inventory.value" :key="item.id" :item="item"/>
            </ul>
        </section>
    </section>
</template>
