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

.login-card {
    align-self: center;
    background: rgba(252, 255, 251, 0.92);
    border: 1px solid rgba(23, 86, 57, 0.18);
    border-radius: 28px;
    padding: clamp(1.25rem, 3vw, 2.5rem);
    box-shadow: 0 20px 60px rgba(14, 65, 42, 0.12);
    backdrop-filter: blur(8px);
}

.brand-block {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    margin-bottom: 0.9rem;
}

.logo-mark {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #8ad441, #159457);
    color: #fff;
    font-size: 0.86rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    transform: rotate(-6deg);
}

.eyebrow {
    font-size: 0.88rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--green-deep);
    font-weight: 700;
}

.brand-subtitle {
    color: var(--green-muted);
    font-size: 0.85rem;
}

h1 {
    font-family: var(--font-display);
    font-size: clamp(1.6rem, 3.8vw, 3rem);
    line-height: 1.08;
    color: var(--green-ink);
    margin-bottom: 0.9rem;
}

.mode-switch {
    display: inline-flex;
    border: 1px solid rgba(21, 83, 51, 0.15);
    border-radius: 999px;
    background: rgba(18, 86, 55, 0.06);
    padding: 0.25rem;
    margin-bottom: 0.85rem;
}

.mode-switch button {
    border: 0;
    background: transparent;
    color: var(--green-ink);
    font-weight: 700;
    border-radius: 999px;
    padding: 0.4rem 0.85rem;
    cursor: pointer;
}

.mode-switch button.active {
    background: #fff;
    box-shadow: 0 3px 8px rgba(15, 75, 45, 0.18);
}

.login-form {
    display: grid;
    gap: 0.55rem;
}

label {
    color: var(--green-ink);
    font-size: 0.9rem;
    font-weight: 600;
}

input {
    border: 1px solid rgba(23, 86, 57, 0.2);
    border-radius: 12px;
    padding: 0.8rem 0.9rem;
    font-size: 1rem;
    background: #fff;
    color: var(--green-ink);
    margin-bottom: 0.35rem;
}

input:focus {
    outline: 2px solid var(--lime-pop);
    outline-offset: 1px;
}

button {
    margin-top: 0.55rem;
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

button:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 28px rgba(9, 102, 58, 0.35);
}

button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.error-msg {
    color: #a12030;
    font-size: 0.88rem;
    font-weight: 600;
    margin-top: 0.7rem;
}

.success-msg {
    color: #0f6f46;
    font-size: 0.88rem;
    font-weight: 600;
    margin-top: 0.7rem;
}

.verification-panel,
.forgot-panel {
    margin-top: 0.9rem;
    padding-top: 0.9rem;
    border-top: 1px dashed rgba(21, 83, 51, 0.2);
}

.panel-title {
    color: var(--green-ink);
    font-weight: 700;
    margin-bottom: 0.45rem;
}

.inline-form {
    display: flex;
    gap: 0.5rem;
}

.inline-form input {
    flex: 1;
    margin: 0;
}

.inline-form button {
    margin: 0;
    font-size: 0.9rem;
    padding: 0.76rem 0.9rem;
    white-space: nowrap;
}

.benefits {
    align-self: center;
    display: grid;
    gap: 0.8rem;
}

.benefits article {
    background: rgba(255, 255, 255, 0.72);
    border: 1px solid rgba(23, 86, 57, 0.16);
    border-radius: 20px;
    padding: 1rem;
}

.benefits h2 {
    font-family: var(--font-display);
    color: var(--green-deep);
    margin-bottom: 0.2rem;
}

.benefits p {
    color: var(--green-muted);
    font-size: 0.95rem;
}

@media (max-width: 980px) {
    .login-page {
        grid-template-columns: 1fr;
        padding-top: 2rem;
    }

    .inline-form {
        flex-direction: column;
    }
}
</style>
