<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref} from 'vue';

withDefaults(
    defineProps<{
        aside?: boolean
    }>(), {
        aside: true
    }
)

const highlightCards = [
    {
        title: 'Pantry-first planning',
        copy: 'See what recipes match your current ingredients before buying more.'
    },
    {
        title: 'Invite your household',
        copy: 'Bring partners, kids, and flatmates into one shared pantry so everyone can add updates in seconds.'
    },
    {
        title: 'Weekly plan sync',
        copy: 'Coordinate meals for the week and auto-build a shopping list from what is missing at home.'
    },
    {
        title: 'Waste less each week',
        copy: 'Track ingredients before they expire and prioritize recipes that use what you already bought.'
    }
];

const activeSlide = ref(0);
const SWIPE_THRESHOLD = 42;
const ROTATE_INTERVAL_MS = 4200;
const INTERACTION_RESUME_DELAY_MS = 3000;
const touchStartX = ref<number | null>(null);
const touchCurrentX = ref<number | null>(null);

let rotateTimer: number | null = null;
let resumeTimer: number | null = null;

function isMobileViewport() {
    return window.matchMedia('(max-width: 767px)').matches;
}

function goToNextSlide() {
    activeSlide.value = (activeSlide.value + 1) % highlightCards.length;
}

function goToPreviousSlide() {
    activeSlide.value = (activeSlide.value - 1 + highlightCards.length) % highlightCards.length;
}

function clearRotateTimer() {
    if (!rotateTimer) {
        return;
    }

    clearInterval(rotateTimer);
    rotateTimer = null;
}

function clearResumeTimer() {
    if (!resumeTimer) {
        return;
    }

    clearTimeout(resumeTimer);
    resumeTimer = null;
}

function startAutoRotate() {
    clearRotateTimer();

    if (!isMobileViewport()) {
        return;
    }

    rotateTimer = window.setInterval(() => {
        goToNextSlide();
    }, ROTATE_INTERVAL_MS);
}

function scheduleAutoRotateResume() {
    if (!isMobileViewport()) {
        return;
    }

    clearRotateTimer();
    clearResumeTimer();
    resumeTimer = window.setTimeout(() => {
        startAutoRotate();
    }, INTERACTION_RESUME_DELAY_MS);
}

function onSwipeStart(event: TouchEvent) {
    const point = event.touches[0];
    if (!point) {
        return;
    }

    touchStartX.value = point.clientX;
    touchCurrentX.value = point.clientX;
    scheduleAutoRotateResume();
}

function onSwipeMove(event: TouchEvent) {
    const point = event.touches[0];
    if (!point || touchStartX.value === null) {
        return;
    }

    touchCurrentX.value = point.clientX;
}

function onSwipeEnd() {
    if (touchStartX.value === null || touchCurrentX.value === null) {
        return;
    }

    const deltaX = touchCurrentX.value - touchStartX.value;

    if (Math.abs(deltaX) >= SWIPE_THRESHOLD) {
        if (deltaX < 0) {
            goToNextSlide();
        } else {
            goToPreviousSlide();
        }
    }

    touchStartX.value = null;
    touchCurrentX.value = null;
}

onMounted(() => {
    startAutoRotate();
});

onBeforeUnmount(() => {
    clearRotateTimer();
    clearResumeTimer();
});
</script>

<template>
    <section
        class="order-2 self-center box-border min-w-0 w-full max-w-[640px] justify-self-center space-y-6 rounded-[28px] border border-emerald-900/20 bg-emerald-50/90 px-5 py-5 shadow-[0_20px_60px_rgba(14,65,42,0.12)] backdrop-blur md:order-1 md:space-y-7 md:px-9 md:py-8 lg:px-10 lg:py-9"
    >
        <slot></slot>
    </section>

    <aside v-if="aside" class="order-1 min-w-0 w-full max-w-[520px] justify-self-center lg:max-w-none md:order-2">
        <div class="md:hidden">
            <div
                class="overflow-hidden rounded-[20px] border border-emerald-900/15 bg-white/70 shadow-[0_10px_28px_rgba(8,72,43,0.12)]"
                @touchstart.passive="onSwipeStart"
                @touchmove.passive="onSwipeMove"
                @touchend="onSwipeEnd"
                @touchcancel="onSwipeEnd"
            >
                <div
                    class="flex transition-transform duration-500 ease-out"
                    :style="{ transform: `translateX(-${activeSlide * 100}%)` }"
                >
                    <article
                        v-for="card in highlightCards"
                        :key="card.title"
                        class="w-full shrink-0 px-4 py-4"
                    >
                        <h2 class="mb-2 font-[var(--font-display)] text-[1.1rem] text-[var(--green-deep)]">{{ card.title }}</h2>
                        <p class="text-[0.92rem] leading-relaxed text-[var(--green-muted)]">{{ card.copy }}</p>
                    </article>
                </div>
            </div>

            <div class="mt-2 flex justify-center gap-2">
                <span
                    v-for="(card, index) in highlightCards"
                    :key="`${card.title}-dot`"
                    class="h-2.5 w-2.5 rounded-full border border-emerald-700/30 transition"
                    :class="index === activeSlide ? 'bg-emerald-700' : 'bg-white/90'"
                />
            </div>
        </div>

        <div class="hidden content-center gap-3 md:grid">
            <article
                v-for="card in highlightCards"
                :key="card.title"
                class="box-border rounded-[20px] border border-emerald-900/15 bg-white/70 px-[1.1rem] py-[1.05rem] md:px-[1.35rem] md:py-[1.25rem]"
            >
                <h2 class="mb-2 font-[var(--font-display)] text-[1.12rem] text-[var(--green-deep)]">{{ card.title }}</h2>
                <p class="text-[0.95rem] leading-relaxed text-[var(--green-muted)]">
                    {{ card.copy }}
                </p>
            </article>
        </div>
    </aside>

</template>
