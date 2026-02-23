<script setup lang="ts">
const props = defineProps<{
    title: string;
    description: string;
    routeName: string;
    icon?: string;
}>();
</script>

<template>
    <router-link
        :to="{name: props.routeName}"
        class="group relative min-w-0 overflow-hidden rounded-2xl border border-emerald-900/15 bg-white/80 p-4 shadow-[0_12px_26px_rgba(10,84,49,0.12)] transition hover:-translate-y-0.5 hover:border-emerald-600/40 hover:shadow-[0_16px_34px_rgba(10,84,49,0.2)]"
    >
        <div
            class="pointer-events-none absolute -right-8 -top-8 h-20 w-20 rounded-full bg-gradient-to-br from-lime-300/50 to-transparent blur-xl"
            aria-hidden="true"
        />
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700/80">{{ props.icon || 'Zone' }}</p>
        <h3 class="break-words font-[var(--font-display)] text-xl leading-tight text-[var(--green-ink)]">{{ props.title }}</h3>
        <p class="mt-2 break-words text-sm leading-relaxed text-[var(--green-muted)]">{{ props.description }}</p>
        <span class="mt-3 inline-flex text-sm font-semibold text-emerald-700 transition group-hover:text-emerald-900">
            Open
        </span>
    </router-link>
</template>
