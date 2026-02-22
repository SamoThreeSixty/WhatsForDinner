<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const tabs = [
    { label: 'Home', path: '/home', key: 'home' },
    { label: 'Pantry', path: '/generate', key: 'pantry' },
    { label: 'Recipe Matches', path: '/results', key: 'recipes' },
    { label: 'Account', path: '/user', key: 'account' }
];

const showShell = computed(() => !route.meta.hideShell && authStore.isLoggedIn);

const logout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>

<template>
    <div class="app-bg"></div>
    <div class="app-grain"></div>

    <header v-if="showShell" class="main-header">
        <div class="brand-wrap">
            <span class="logo-mark" aria-hidden="true">WFD</span>
            <div>
                <p class="brand-label">WhatsForDinner</p>
                <h1>Fuel Better</h1>
            </div>
        </div>

        <nav class="tabs" aria-label="Primary navigation">
            <router-link
                v-for="tab in tabs"
                :key="tab.path"
                :to="tab.path"
                class="tab-link"
                :class="{ active: route.path === tab.path }"
            >
                {{ tab.label }}
            </router-link>
        </nav>

        <div class="header-actions">
            <router-link class="cta-pill" to="/generate">+ Start New Recipe</router-link>
            <button class="ghost-btn" type="button" @click="logout">Logout</button>
        </div>
    </header>

    <router-view></router-view>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Fraunces:opsz,wght@9..144,600;9..144,800&display=swap');

:root {
    --font-display: 'Fraunces', serif;
    --font-body: 'Manrope', sans-serif;
    --green-ink: #113c2d;
    --green-deep: #0f7b4b;
    --green-solid: #1a9a5d;
    --green-muted: #436759;
    --green-panel: rgba(242, 252, 246, 0.88);
    --lime-pop: #b8ef4f;
    --cream: #f8fdf8;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

:where(body, h1, h2, h3, h4, h5, h6, p) {
    margin: 0;
}

html,
body,
#app {
    min-height: 100%;
}

body {
    font-family: var(--font-body);
    color: var(--green-ink);
    background: #d8f2d4;
}

.app-bg {
    position: fixed;
    inset: 0;
    z-index: -2;
    background:
        radial-gradient(circle at 20% 18%, rgba(184, 239, 79, 0.5), transparent 38%),
        radial-gradient(circle at 84% 7%, rgba(82, 191, 116, 0.45), transparent 45%),
        linear-gradient(145deg, #ecfae8 0%, #c7ecbf 45%, #dff4d6 100%);
}

.app-grain {
    position: fixed;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    opacity: 0.2;
    background-image: radial-gradient(rgba(21, 76, 49, 0.4) 0.45px, transparent 0.45px);
    background-size: 3px 3px;
}

.main-header {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 1rem;
    margin: 1rem;
    background: rgba(252, 255, 251, 0.85);
    border: 1px solid rgba(17, 76, 52, 0.2);
    border-radius: 22px;
    padding: 0.9rem 1.1rem;
    backdrop-filter: blur(8px);
}

.brand-wrap {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.logo-mark {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: linear-gradient(135deg, #8ad441, #159457);
    color: #fff;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    transform: rotate(-6deg);
}

.brand-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--green-muted);
}

.main-header h1 {
    font-family: var(--font-display);
    font-size: 1.45rem;
    line-height: 1;
    color: var(--green-ink);
}

.tabs {
    justify-self: center;
    display: inline-flex;
    gap: 0.3rem;
    background: rgba(22, 106, 62, 0.08);
    border-radius: 999px;
    padding: 0.35rem;
}

.tab-link {
    text-decoration: none;
    color: var(--green-ink);
    font-weight: 700;
    font-size: 0.88rem;
    padding: 0.5rem 0.82rem;
    border-radius: 999px;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.tab-link:hover {
    transform: translateY(-1px);
}

.tab-link.active {
    background: #fff;
    box-shadow: 0 4px 12px rgba(12, 84, 48, 0.18);
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 0.55rem;
}

.cta-pill {
    text-decoration: none;
    background: linear-gradient(120deg, var(--green-solid), var(--green-deep));
    color: #fff;
    border-radius: 999px;
    padding: 0.54rem 0.9rem;
    font-size: 0.85rem;
    font-weight: 800;
}

.ghost-btn {
    background: transparent;
    border: 1px solid rgba(17, 76, 52, 0.28);
    border-radius: 999px;
    color: var(--green-ink);
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.48rem 0.75rem;
    cursor: pointer;
}

@media (max-width: 1060px) {
    .main-header {
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }

    .tabs {
        width: 100%;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    .header-actions {
        justify-content: flex-end;
    }
}
</style>
