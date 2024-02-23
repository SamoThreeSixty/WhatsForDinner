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
        background-color: #0DD98B;
        border-top: none;
        border-left: none;
        border-bottom: 3px solid #005348;
        border-right: 3px solid #005348;
        text-decoration: none;

        transition: all 0.5s;
    }
    a:hover {
        background-color: #0feb96;
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
        background-color: #f2f2f2;
        width: 90%;
        margin: 0 auto;
        height: 100px;
        font-size: 3rem;
        border: none;
        border-bottom: 4px solid #00B59C; 
    }
</style>