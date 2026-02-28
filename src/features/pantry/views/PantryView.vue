<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import SearchSelection from '@/components/ui/SearchSelection.vue';
import PantryItem from "@/features/pantry/components/PantryItem.vue";
import Input from "@/components/ui/Input.vue";
import {useInventoryList} from "@/features/pantry/composable/useInventry.ts";
import {useIngredient} from "@/features/pantry/composable/useIngredient.ts";
import {useProduct} from "@/features/pantry/composable/useProduct.ts";

const inventory = useInventoryList();
onMounted(() => inventory.getInventoryItems());

const ingredient = useIngredient();
onMounted(() => ingredient.loadIngredientOptions());

const product = useProduct();

watch(ingredient.selectedIngredient, async (ingredientId) => {
    product.selectedProduct.value = '';
    product.query.value = '';
    product.selectedIngredient.value = ingredientId;
});

onBeforeUnmount(() => {
    ingredient.clearTimer();
    product.clearTimer();
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

                <SearchSelection
                    v-if="ingredient.selectedIngredient.value !== null"
                    v-model="product.selectedProduct.value"
                    class="md:col-span-2"
                    :options="product.productOptions.value"
                    :loading="product.loading.value"
                    placeholder="Type product name to search"
                    no-results-text="No products found"
                    @search="product.onProductSearchInput"
                />

            </form>

        </section>

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
                @input="onListSearchInput"
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
