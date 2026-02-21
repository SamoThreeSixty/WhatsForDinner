<script setup>
    import { useRouter } from 'vue-router';
    import Results from '../components/Results.vue';

    const router = useRouter();

</script>
<template>
    <router-link to="/">Back to start</router-link>

    <div class="section">
        <h2>Account</h2>
        <p>
            Welcome Sam to your account manager<br>
            Fancy having a look at your previous recipies...
        </p>
        <form>
            <input placeholder="Search that amazing recipie" />
        </form>

        <Results :titles="['Cream of mushroom soup with smoked paprika and brown rice', 'Cream of mushroom soup with smoked paprika and brown rice', 'Cream of mushroom soup with smoked paprika and brown rice']" />
    </div>


</template>
<style scoped>
    a{
        position: absolute;
        font-size: 1.5rem;
        border-radius: 10px;
        border: none;
        margin: 0.5rem 1rem;
        padding: 5px 20px;
        background-color: var(--accent);
        border-top: none;
        border-left: none;
        border-bottom: 3px solid var(--accent-deep);
        border-right: 3px solid var(--accent-deep);
        text-decoration: none;

        box-shadow: var(--shadow-soft);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    a:hover {
        background-color: #0feb96;
        transform: translateY(-2px);
        box-shadow: var(--shadow-strong);
        cursor: pointer;
    }
    .section {
        display: flex;
        flex-direction: column;
        max-width: 1200px;
        height: 100%;
        margin: 0 auto;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    form {
        display: flex;
        justify-content: center;
        margin-top: 1rem;
        width: 100%;
    }

    input {
        background-color: var(--surface);
        width: 90%;
        margin: 0 auto;
        height: 100px;
        font-size: 3rem;
        border: none;
        border-bottom: 4px solid var(--accent-strong); 
    }
</style>
