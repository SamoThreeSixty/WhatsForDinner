<script setup lang="ts">
import type {InventoryItem} from "@/features/pantry/types/inventry.ts";

const props = defineProps<{
    item: InventoryItem;
}>();

const emit = defineEmits<{
    (e: 'delete', id: number): void
}>();
</script>

<template>
    <li class="flex items-start justify-between gap-3 rounded-xl border border-emerald-900/10 bg-emerald-50/40 px-3 py-2.5">
        <div class="min-w-0">
<!--            TODO: Display item      -->
<!--            <p class="truncate text-sm font-semibold text-(&#45;&#45;green-ink)">{{ item.product.name }}</p>-->
<!--            <p class="text-xs text-(&#45;&#45;green-muted)">-->
<!--                {{ item.quantity }} {{ item.unit }} · {{ item.unit_type }}-->
<!--                <span v-if="item.location"> · {{ item.location }}</span>-->
<!--                <span v-if="item.category"> · {{ item.category }}</span>-->
<!--            </p>-->
        </div>
        <div class="shrink-0 flex gap-2">
            <button type="button" class="text-xs font-semibold text-emerald-800 hover:underline">
                Edit
            </button>
            <button type="button" class="text-xs font-semibold text-rose-700 hover:underline">
                Delete
            </button>
        </div>
    </li>
</template>

