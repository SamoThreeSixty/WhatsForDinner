<script setup lang="ts">
import {computed, ref} from 'vue';
import {useRouter, useRoute} from 'vue-router';
import {useAuthStore} from '@/stores/auth';
import {useFeedbackStore} from '@/stores/feedback';
import AuthFeedback from '@/features/auth/components/AuthFeedback.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const feedbackStore = useFeedbackStore();

const isLoggingOut = ref(false);
const isMobileMenuOpen = ref(false);
const isUserMenuOpen = ref(false);
const layoutFeedbackScope = 'dashboard:layout';

const userInitial = computed(() => (authStore.username?.charAt(0) || 'U').toUpperCase());
const layoutFeedback = computed(() => feedbackStore.forScope(layoutFeedbackScope));

const navItems = [
    {name: 'app.dashboard', label: 'Dashboard'},
];

function isActive(name: string) {
    return route.name === name;
}

function toggleMobileMenu() {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (!isMobileMenuOpen.value) {
        isUserMenuOpen.value = false;
    }
}

function closeMobileMenu() {
    isMobileMenuOpen.value = false;
    isUserMenuOpen.value = false;
}

function toggleUserMenu() {
    isUserMenuOpen.value = !isUserMenuOpen.value;
}

async function goToRoute(name: string) {
    await router.push({name});
    closeMobileMenu();
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
        closeMobileMenu();
        await router.push({name: 'auth.login'});
    } catch {
        feedbackStore.error(layoutFeedbackScope, 'Unable to logout right now. Please try again.');
    } finally {
        isLoggingOut.value = false;
    }
}
</script>

<template>
    <main class="mx-auto box-border grid min-w-0 min-h-dvh w-full max-w-300 gap-4 px-3 py-3 sm:px-4 md:grid-cols-[220px_1fr] md:gap-5 md:px-6 md:py-5 lg:grid-cols-[250px_1fr]">
        <header class="sticky top-1 z-20 flex items-center gap-1.5 rounded-lg border border-emerald-900/10 bg-white/80 px-2 py-1.5 shadow-[0_6px_14px_rgba(8,72,43,0.1)] backdrop-blur md:hidden">
            <button
                type="button"
                class="grid h-8 w-8 place-items-center rounded-md border border-emerald-900/15 bg-white text-emerald-900 shadow-[0_3px_8px_rgba(8,72,43,0.1)]"
                aria-label="Toggle menu"
                @click="toggleMobileMenu"
            >
                <span class="relative block h-3 w-4">
                    <span
                        class="absolute left-0 top-0 h-0.5 w-4 rounded bg-current transition"
                        :class="isMobileMenuOpen ? 'translate-y-1.25 rotate-45' : ''"
                    />
                    <span
                        class="absolute left-0 top-1.25 h-0.5 w-4 rounded bg-current transition"
                        :class="isMobileMenuOpen ? 'opacity-0' : ''"
                    />
                    <span
                        class="absolute left-0 top-2.5 h-0.5 w-4 rounded bg-current transition"
                        :class="isMobileMenuOpen ? '-translate-y-1.25 -rotate-45' : ''"
                    />
                </span>
            </button>
            <p class="text-[0.66rem] font-bold tracking-[0.01em] text-(--green-ink)">WhatsForDinner</p>
        </header>

        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 z-30 bg-emerald-950/35 md:hidden"
            @click="closeMobileMenu"
        />

        <aside
            class="fixed left-0 top-0 z-40 flex h-dvh w-[84%] max-w-[320px] min-w-0 flex-col rounded-r-3xl border-r border-emerald-900/15 bg-white p-4 shadow-[0_16px_36px_rgba(8,72,43,0.22)] transition-transform md:static md:z-auto md:h-auto md:w-auto md:max-w-none md:translate-x-0 md:rounded-3xl md:border md:bg-white/70 md:shadow-[0_16px_36px_rgba(8,72,43,0.12)] md:backdrop-blur"
            :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-emerald-800/10 bg-emerald-50/80 p-3">
                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-linear-to-br from-lime-400 to-emerald-600 text-sm font-extrabold text-white">
                    {{ userInitial }}
                </span>
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-(--green-ink)">{{ authStore.username || 'User' }}</p>
                    <p class="truncate text-xs text-(--green-muted)">{{ authStore.email }}</p>
                </div>
            </div>

            <nav class="min-w-0 grid gap-1">
                <router-link
                    v-for="item in navItems"
                    :key="item.name"
                    :to="{name: item.name}"
                    class="min-w-0 rounded-xl border px-3 py-2 text-sm font-semibold transition"
                    :class="isActive(item.name)
                        ? 'border-emerald-700 bg-emerald-700 text-white'
                        : 'border-emerald-900/10 bg-white/70 text-emerald-800 hover:border-emerald-700/35 hover:bg-emerald-50'"
                    @click="closeMobileMenu"
                >
                    {{ item.label }}
                </router-link>
            </nav>

            <div class="mt-auto pt-4">
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-between rounded-xl border border-emerald-900/20 bg-white px-3 py-2 text-sm font-semibold text-emerald-900 transition hover:border-emerald-900/35 hover:bg-emerald-50"
                    @click="toggleUserMenu"
                >
                    <span>User menu</span>
                    <span class="text-base leading-none">{{ isUserMenuOpen ? '▾' : '▴' }}</span>
                </button>

                <div class="relative mt-2">
                    <div
                        v-if="isUserMenuOpen"
                        class="absolute inset-x-0 bottom-full z-10 mb-2 grid gap-1 rounded-xl border border-emerald-900/12 bg-white/95 p-2 shadow-[0_14px_30px_rgba(8,72,43,0.22)] backdrop-blur"
                    >
                        <button
                            type="button"
                            class="rounded-lg border border-transparent px-3 py-2 text-left text-sm font-medium text-emerald-900 hover:border-emerald-900/15 hover:bg-emerald-50/70"
                            @click="goToRoute('app.account')"
                        >
                            Account
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-transparent px-3 py-2 text-left text-sm font-medium text-emerald-900 hover:border-emerald-900/15 hover:bg-emerald-50/70"
                            @click="goToRoute('app.settings')"
                        >
                            Settings
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-transparent px-3 py-2 text-left text-sm font-medium text-rose-700 hover:border-rose-700/20 hover:bg-rose-50"
                            :disabled="isLoggingOut"
                            @click="logout"
                        >
                            {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
                        </button>
                    </div>
                </div>

                <AuthFeedback :feedback="layoutFeedback" />
            </div>
        </aside>

        <section class="min-w-0 space-y-4">
            <router-view />
        </section>
    </main>
</template>
