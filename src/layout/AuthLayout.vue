<script setup>

</script>

<template>
    <main class="login-page">
        <router-view></router-view>
    </main>
</template>


<style scoped>
.login-page {
    min-height: 100dvh;
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 1.5rem;
    padding: clamp(1rem, 3vw, 2.25rem);
}

.auth-primary-btn {
    border: 0;
    border-radius: 999px;
    background: linear-gradient(120deg, var(--green-solid), var(--green-deep));
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    padding: 0.82rem 1.2rem;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.auth-primary-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 28px rgba(9, 102, 58, 0.35);
}

.auth-primary-btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.auth-link-btn {
    color: var(--green-deep);
    font-weight: 700;
    text-decoration: none;
}

.auth-link-btn:hover {
    text-decoration: underline;
}

</style>
