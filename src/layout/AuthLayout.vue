<template>
    <main class="auth-shell">
        <router-view />
    </main>
</template>


<style scoped>
.auth-shell {
    min-height: 100dvh;
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    box-sizing: border-box;
    width: min(1100px, 100%);
    margin: 0 auto;
    padding: clamp(0.95rem, 3vw, 2.25rem);
    align-content: center;
    align-items: center;
}

@media (min-width: 980px) {
    .auth-shell {
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        gap: clamp(1rem, 2.5vw, 2rem);
    }
}
</style>
