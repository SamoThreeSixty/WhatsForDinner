<script setup lang="ts">
withDefaults(
    defineProps<{
        eyebrow?: string;
        title: string;
        subtitle: string;
    }>(),
    {
        eyebrow: 'Workspace'
    }
);
</script>

<template>
    <header class="dashboard-header relative min-w-0 overflow-hidden rounded-3xl border border-emerald-900/15 bg-(--green-panel) px-4 py-4 shadow-[0_14px_30px_rgba(8,72,43,0.12)] sm:px-5 md:px-7 md:py-6">
        <div class="pointer-events-none absolute -left-16 -top-10 h-36 w-36 rounded-full bg-lime-300/30 blur-2xl" aria-hidden="true" />
        <div class="pointer-events-none absolute -right-10 -bottom-12 h-40 w-40 rounded-full bg-emerald-300/25 blur-2xl" aria-hidden="true" />

        <p class="text-[0.66rem] font-semibold uppercase tracking-[0.13em] text-emerald-700/80 md:text-xs">
            {{ eyebrow }}
        </p>

        <h1 class="dashboard-title mt-1.5 wrap-break-word font-(--font-display) text-[clamp(1.3rem,4.9vw,2.35rem)] leading-[1.08] text-(--green-ink)">
            {{ title }}
        </h1>

        <p class="dashboard-subtitle mt-2.5 max-w-2xl text-[0.89rem] leading-relaxed text-(--green-muted) md:text-[0.98rem]">
            {{ subtitle }}
        </p>
    </header>
</template>

<style scoped>
@media (max-width: 767px) {
    .dashboard-header {
        min-height: 172px;
        max-height: 172px;
    }

    .dashboard-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dashboard-subtitle {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
}
</style>
