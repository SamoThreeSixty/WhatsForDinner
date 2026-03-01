<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref} from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import PantryItem from "@/features/pantry/components/PantryItem.vue";
import Input from "@/components/ui/Input.vue";
import PantryAddModal from "@/features/pantry/components/PantryAddModal.vue";
import {useInventoryList} from "@/features/pantry/composable/useInventry.ts";
import type {InventoryItem} from "@/features/pantry/types/inventry.ts";

const inventory = useInventoryList();
onMounted(() => inventory.getInventoryItems());

const isAddModalOpen = ref<boolean>(false);

async function onInventoryItemCreated(createdItem: InventoryItem) {
    inventory.inventory.value.unshift(createdItem);
}

onBeforeUnmount(() => {
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
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xs font-semibold uppercase tracking-[0.11em] text-emerald-800/85">All items</h2>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold"
                        @click="isAddModalOpen = true"
                    >
                        Add
                    </button>
                    <button type="button"
                            class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold"
                            :disabled="inventory.loading.value"
                            @click="inventory.getInventoryItems()"
                    >
                        {{ inventory.loading.value ? 'Refreshing...' : 'Refresh' }}
                    </button>
                </div>
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

        <PantryAddModal
            v-model="isAddModalOpen"
            @created="onInventoryItemCreated"
        />
    </section>
</template>
