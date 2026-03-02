<script setup lang="ts">
import {computed, ref, watch} from 'vue';
import {useRouter, useRoute} from 'vue-router';
import {useAuthStore} from '@/stores/auth';
import {useHouseholdStore} from '@/stores/household';
import {useFeedbackStore} from '@/stores/feedback';
import AuthFeedback from '@/features/auth/components/AuthFeedback.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const householdStore = useHouseholdStore();
const feedbackStore = useFeedbackStore();

const isLoggingOut = ref(false);
const isUserMenuOpen = ref(false);
const layoutFeedbackScope = 'dashboard:layout';

const userInitial = computed(() => (authStore.username?.charAt(0) || 'U').toUpperCase());
const layoutFeedback = computed(() => feedbackStore.forScope(layoutFeedbackScope));

const navItems = [
    {name: 'app.dashboard', label: 'Dashboard', shortLabel: 'Home'},
    {name: 'app.pantry', label: 'Pantry', shortLabel: 'Pantry'},
    {name: 'app.recipes', label: 'Recipes', shortLabel: 'Recipes'},
    {name: 'app.household_manage', label: 'Household', shortLabel: 'Household'},
];

function isActive(name: string) {
    return route.name === name;
}

function toggleUserMenu() {
    isUserMenuOpen.value = !isUserMenuOpen.value;
}

function closeUserMenu() {
    isUserMenuOpen.value = false;
}

async function goToRoute(name: string) {
    await router.push({name});
    closeUserMenu();
}

async function logout() {
    if (isLoggingOut.value) {
        return;
    }

    const confirmed = window.confirm('Are you sure you want to logout?');
    if (!confirmed) {
        return;
    }

    isLoggingOut.value = true;
    feedbackStore.clear(layoutFeedbackScope);

    try {
        await authStore.logout();
        closeUserMenu();
        await router.push({name: 'auth.login'});
    } catch {
        feedbackStore.error(layoutFeedbackScope, 'Unable to logout right now. Please try again.');
    } finally {
        isLoggingOut.value = false;
    }
}

watch(
    () => route.fullPath,
    () => {
        closeUserMenu();
    }
);
</script>

<template>
    <main class="mx-auto box-border min-h-dvh w-full max-w-300 px-3 pb-24 pt-3 sm:px-4 md:px-6 md:pb-6 md:pt-5">
        <header class="sticky top-2 z-40 mb-3 flex items-center justify-between rounded-2xl border border-emerald-900/12 bg-white/88 px-3 py-2 shadow-[0_10px_24px_rgba(8,72,43,0.12)] backdrop-blur md:mb-4 md:px-4 md:py-2.5">
            <div class="min-w-0">
                <p class="truncate text-[0.72rem] font-black tracking-[0.08em] text-(--green-muted)">WHATSFORDINNER</p>
                <p class="truncate text-sm font-bold text-(--green-ink)">Plan around what you already have</p>
            </div>

            <div class="relative ml-3 shrink-0">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-900/18 bg-white px-2.5 py-1.5 text-emerald-900 shadow-[0_5px_12px_rgba(8,72,43,0.1)] transition hover:border-emerald-900/30 hover:bg-emerald-50"
                    @click="toggleUserMenu"
                >
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-linear-to-br from-lime-400 to-emerald-600 text-xs font-extrabold text-white">
                        {{ userInitial }}
                    </span>
                    <span class="hidden max-w-28 truncate text-xs font-semibold md:block">{{ authStore.username || 'User' }}</span>
                    <span class="text-xs leading-none">{{ isUserMenuOpen ? '▴' : '▾' }}</span>
                </button>

                <div
                    v-if="isUserMenuOpen"
                    class="absolute right-0 top-[calc(100%+0.45rem)] z-50 grid min-w-[220px] gap-1 rounded-2xl border border-emerald-900/12 bg-white/98 p-2 shadow-[0_16px_34px_rgba(8,72,43,0.2)] backdrop-blur"
                >
                    <p class="mb-1 rounded-xl bg-emerald-50/70 px-2.5 py-2 text-xs font-medium text-emerald-900/80">
                        {{ authStore.email }}
                    </p>

                    <button
                        v-if="householdStore.hasMultipleHouseholds"
                        type="button"
                        class="rounded-lg border border-transparent px-3 py-2 text-left text-sm font-medium text-emerald-900 transition hover:border-emerald-900/15 hover:bg-emerald-50/70"
                        @click="goToRoute('auth.access')"
                    >
                        Switch household
                    </button>

                    <button
                        type="button"
                        class="rounded-lg border border-transparent px-3 py-2 text-left text-sm font-medium text-rose-700 transition hover:border-rose-700/20 hover:bg-rose-50"
                        :disabled="isLoggingOut"
                        @click="logout"
                    >
                        {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
                    </button>
                </div>
            </div>
        </header>

        <div
            v-if="isUserMenuOpen"
            class="fixed inset-0 z-30"
            @click="closeUserMenu"
        />

        <nav class="sticky top-[4.8rem] z-20 mb-4 hidden grid-cols-4 gap-2 rounded-2xl border border-emerald-900/10 bg-white/76 p-2 shadow-[0_8px_20px_rgba(8,72,43,0.1)] backdrop-blur md:grid lg:max-w-[760px]">
            <router-link
                v-for="item in navItems"
                :key="item.name"
                :to="{name: item.name}"
                class="rounded-xl border px-3 py-2 text-center text-sm font-semibold transition"
                :class="isActive(item.name)
                    ? 'border-emerald-700 bg-emerald-700 text-white shadow-[0_10px_18px_rgba(8,72,43,0.22)]'
                    : 'border-emerald-900/12 bg-white/75 text-emerald-900 hover:border-emerald-700/35 hover:bg-emerald-50'"
            >
                {{ item.label }}
            </router-link>
        </nav>

        <section class="min-w-0 space-y-4">
            <router-view />
            <AuthFeedback :feedback="layoutFeedback" />
        </section>

        <nav class="fixed inset-x-3 bottom-3 z-40 grid grid-cols-4 gap-1.5 rounded-2xl border border-emerald-900/15 bg-white/92 p-1.5 shadow-[0_16px_28px_rgba(8,72,43,0.2)] backdrop-blur md:hidden">
            <router-link
                v-for="item in navItems"
                :key="item.name"
                :to="{name: item.name}"
                class="rounded-xl border px-2 py-2 text-center text-[0.72rem] font-semibold transition"
                :class="isActive(item.name)
                    ? 'border-emerald-700 bg-emerald-700 text-white'
                    : 'border-transparent bg-emerald-50/55 text-emerald-900 hover:border-emerald-700/20 hover:bg-emerald-100/70'"
            >
                {{ item.shortLabel }}
            </router-link>
        </nav>
    </main>
</template>
