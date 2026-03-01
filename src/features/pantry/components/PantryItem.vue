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
            <p class="truncate text-sm font-semibold text-(--green-ink)">
                {{ item.product.company ? `${item.product.company} ${item.product.name}` : item.product.name }}
            </p>
            <p class="text-xs text-(--green-muted)">
                {{ item.quantity }} {{ item.unit }}
                <span v-if="item.location"> · {{ item.location }}</span>
                <span v-if="item.purchased_at"> · Bought {{ item.purchased_at }}</span>
                <span v-if="item.expires_at"> · Expires {{ item.expires_at }}</span>
            </p>
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
