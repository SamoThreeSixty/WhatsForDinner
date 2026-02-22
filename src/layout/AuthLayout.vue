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
</style>
