<script setup lang="ts">

withDefaults(
    defineProps<{
        aside?: boolean
    }>(), {
        aside: true
    }
)

</script>

<template>
    <section
        class="self-center box-border space-y-6 rounded-[28px] border border-emerald-900/20 bg-emerald-50/90 px-[1.5rem] py-[1.5rem] shadow-[0_20px_60px_rgba(14,65,42,0.12)] backdrop-blur md:space-y-7 md:px-[2.1rem] md:py-[2rem] lg:px-[2.5rem] lg:py-[2.3rem]"
    >
        <slot></slot>
    </section>

    <aside v-if="aside" class="grid content-center gap-3">
        <article
            class="box-border rounded-[20px] border border-emerald-900/15 bg-white/70 px-[1.1rem] py-[1.05rem] md:px-[1.35rem] md:py-[1.25rem]">
            <h2 class="mb-2 font-[var(--font-display)] text-[1.12rem] text-[var(--green-deep)]">Pantry-first
                planning</h2>
            <p class="text-[0.95rem] leading-relaxed text-[var(--green-muted)]">See what recipes match your current
                ingredients before buying more.</p>
        </article>
        <article
            class="box-border rounded-[20px] border border-emerald-900/15 bg-white/70 px-[1.1rem] py-[1.05rem] md:px-[1.35rem] md:py-[1.25rem]">
            <h2 class="mb-2 font-[var(--font-display)] text-[1.12rem] text-[var(--green-deep)]">Verified users only</h2>
            <p class="text-[0.95rem] leading-relaxed text-[var(--green-muted)]">
                Email activation helps prevent fake accounts and protects your app from spam signups.
            </p>
        </article>
        <article
            class="box-border rounded-[20px] border border-emerald-900/15 bg-white/70 px-[1.1rem] py-[1.05rem] md:px-[1.35rem] md:py-[1.25rem]">
            <h2 class="mb-2 font-[var(--font-display)] text-[1.12rem] text-[var(--green-deep)]">Rate-limited auth</h2>
            <p class="text-[0.95rem] leading-relaxed text-[var(--green-muted)]">
                Sign-up, login, and reset actions are throttled to reduce brute-force and abuse attempts.
            </p>
        </article>
    </aside>

</template>
