<script setup>
    import { ref, defineProps, onMounted } from 'vue';

    const props = defineProps(['results'])

    const usedIngredients = (ingredients) => {
        let list = [];

        ingredients.forEach((item) => {
            list.push(item.name)
        })

        list[0] = list[0].charAt(0).toUpperCase() + list[0].slice(1);

        
        return list.join(', ');
    }

    const missedIngredients = (ingredients) => {
        let list = [];

        ingredients.forEach((item) => {
            list.push(item.name)
        })
        
        return list.join(', ');
    }

</script>

<template>
    <div class="results-wrapper">
        <div class="results-card" v-for="(result, i) in props.results">
            <div class="card-image">
                <img :src="result.image" class="result-image" alt=""> 
            </div>
            <div class="card-content">
                <h4>{{result.title}}</h4>
                <p class="amount-note">You have {{ result.usedIngredients.length }} of the {{ result.usedIngredients.length + result.missedIngredients.length }} ingredients required</p>
                <div v-if="result.missedIngredients.length >= 1">
                    <p style="text-align: center;"><strong>Recipie ingredients:</strong></p>
                    <span><strong>{{ usedIngredients(result.usedIngredients) }}</strong></span>
                    <span v-if="result.missedIngredients.length > 1">, {{ missedIngredients(result.missedIngredients) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .results-wrapper {
        padding-top: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: start;
        height: 70%;

        padding-right: 10%;
        padding-left: 10%;
    }

    .results-card:nth-child(2n + 1) {
        background-color: #b1f09a9c;
    }

    .results-card {
        position: relative;
        display: grid;
        grid-template-columns: 1fr 2fr;
        padding: 2rem 2rem;
        font-size: 1.5rem;

        .amount-note {
            color: #0e0e0e;
            text-align: center;
            font-style: italic;
            font-size: smaller;
        }
        h4{
            text-align: center;
            font-size: 1.7rem;
        }
        p {
            padding: 0.5rem 10px
        }
        ul {
            width: auto;

            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 1rem 20px;

            li {
                list-style: none;
                background-color: #00B59C;
                border-radius: 10px;
                padding: 0.2rem 0.5rem;
                text-transform: capitalize;
            }
        }

        button {
            align-self: center;
            height: 65px;
            width: 90%;
            font-size: 2rem;
            border-radius: 10px;
            border: none;
            margin: 0.5rem 1rem;
            background-color: #96EE77;
            border-top: none;
            border-left: none;
            border-bottom: 3px solid #005348;
            border-right: 3px solid #005348;
            margin-top: auto;

            transition: all 0.5s;
        }
        button:hover {
            background-color: #0feb96;
            cursor: pointer;
        }
    }

    .results-card:first::before{
        content: '';
        position: absolute;
        left: 10%;
        right: 10%;
        top: 0;
        border-top: 1px solid #005348;

    }

    .results-card::after{
        content: '';
        position: absolute;
        bottom: 0;
        right: 10%;
        left: 10%;
        border-bottom: 1px solid #005348;
    }

    .card-image {
        flex-basis: 25%;

        img {
            width: 100%;
        }
    }
    .card-content {
        padding: 1rem;
        flex-basis: 75%;
        display: flex;
        flex-direction: column;
        align-items: self-start;
    }

    @media screen and (max-width: 1000px) {
        .results-card {
            grid-template-columns: 1fr;
            justify-items: center;
        }

        .results-wrapper {
            padding-right: 5%;
            padding-left: 5%;
        }
    }
</style>