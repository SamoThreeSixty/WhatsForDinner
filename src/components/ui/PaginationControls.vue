<script setup lang="ts">
import {computed} from 'vue';

const props = withDefaults(defineProps<{
    currentPage: number;
    lastPage: number;
    total: number;
    loading?: boolean;
}>(), {
    loading: false,
});

const emit = defineEmits<{
    (event: 'change', page: number): void;
}>();

const pageNumbers = computed<number[]>(() => {
    const start = Math.max(1, props.currentPage - 2);
    const end = Math.min(props.lastPage, props.currentPage + 2);
    const pages: number[] = [];

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    return pages;
});

function go(page: number) {
    if (props.loading || page < 1 || page > props.lastPage || page === props.currentPage) {
        return;
    }

    emit('change', page);
}
</script>

<template>
    <div v-if="props.lastPage > 1" class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-emerald-900/12 bg-emerald-50/35 p-3">
        <p class="text-xs font-medium text-emerald-900/80">
            {{ props.total }} recipes
        </p>

        <div class="flex items-center gap-1.5">
            <button
                type="button"
                class="rounded-lg border border-emerald-900/20 px-2.5 py-1 text-xs font-semibold"
                :disabled="props.loading || props.currentPage <= 1"
                @click="go(props.currentPage - 1)"
            >
                Prev
            </button>

            <button
                v-for="page in pageNumbers"
                :key="page"
                type="button"
                class="rounded-lg border px-2.5 py-1 text-xs font-semibold"
                :class="page === props.currentPage
                    ? 'border-emerald-900/45 bg-emerald-900 text-white'
                    : 'border-emerald-900/20 bg-white text-emerald-900'"
                :disabled="props.loading || page === props.currentPage"
                @click="go(page)"
            >
                {{ page }}
            </button>

            <button
                type="button"
                class="rounded-lg border border-emerald-900/20 px-2.5 py-1 text-xs font-semibold"
                :disabled="props.loading || props.currentPage >= props.lastPage"
                @click="go(props.currentPage + 1)"
            >
                Next
            </button>
        </div>
    </div>
</template>

