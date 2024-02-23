<script setup>
    import { ref, watch, onMounted } from 'vue';
    import router from '../../routes';
    import { useItemsStore } from '../../stores/items.js';

    const itemsStore = useItemsStore();

    let suggestedItems = ref([]);
    let itemsList = ref([]);
    let currentSearch = ref();
    let generating = ref(true)

    onMounted(() => {
        suggestedItems.value = ['pear', 'apple','pear', 'apple','pear', 'apple']

    })

    watch(currentSearch, async (oldSearch, newSearch) => {
        // suggestedItems.value.push(newSearch)
    })

    const addIngredient = () => {
        if(itemsList.value.length < 7 && currentSearch.value !== '' && currentSearch.value) {
            itemsList.value.push(currentSearch.value)
            currentSearch.value = '';
        } else {
            console.log("handle error")
        }
    }

    const handleRemove = (index) => {
        itemsList.value.splice(index, 1);
    }

    const handleGenerateMeal = () => {
        if(itemsList.value.length >= 1) {
            generating.value = false;
            itemsStore.addArray(itemsList.value)
            setTimeout(function() {
                router.push('/results')
            }, 1000)
        } else {
            console.log("add some items")
        }
    }

    
</script>

<template>

    <router-link class="router-btn" to="/">Back to start</router-link>

    <!-- Generate Results -->
    <div class="select-ingredients">
        <Transition name="slide-left">
            <div v-show="generating" class="ingredient-search">
                <div>
                    <form v-on:submit="addIngredient" @submit.prevent="e=>{}" >
                        <p>Select up to  7 different ingredients to make up the perfect recipie!</p>
                        <label>Search your ingredients</label>
                        <input name="ingridents" placeholder="Ingredients..." v-model="currentSearch"/>
                    </form>
                </div>
                <div class="suggestions">
                    <div v-for="(item, i) in suggestedItems" :key="i">
                        {{ item }}
                    </div>
                </div>
            </div>
        </Transition>
        <Transition name="slide-right">
            <div v-show="generating" class="ingredient-list">
                <table>
                    <thead>
                        <h4>Your Items</h4>
                    </thead>
                    <tbody>
                        <tr v-for="(listItem, i) in itemsList" :key="i">
                            <td>{{ listItem }}</td>
                            <td style="color: red;"><a @click="handleRemove(i)">&cross;</a></td>
                        </tr>
                    </tbody>
                    <button @click="handleGenerateMeal">Generate Meals!</button>
                </table>
            </div>
        </Transition>
    </div>
</template>
<style scoped>
    .router-btn{
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
    .router-btn:hover {
        background-color: #0feb96;
        cursor: pointer;
    }
    .select-ingredients {
        display: flex;
        max-width: 1200px;
        height: 100%;
        margin: 0 auto;
        align-items: center;

    }
    .ingredient-search {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding-bottom: 15%;


        div p{
            font-size: 1.2rem;
            margin: 2rem 0;
        }
        label {
            width: 90%;
            margin: 0 auto;
            font-size: 2rem;
            font-weight: bold;
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
    }
    .suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 1rem;
        max-width: 86%;
        align-content: flex-start;

        div {
            background-color: #00B59C;
            border-radius: 10px;
            padding: 0.2rem 0.7rem;
        }
    }
    .ingredient-list {
        flex-basis: 40%;

        table {
            background-color: #f2f2f2;
            font-size: 2rem;
            display: grid;
            grid-template-rows: 70px 1fr 80px;
            min-height: 500px;
            border-top: none;
            border-left: none;
            border-bottom: 3px solid #005348;
            border-right: 3px solid #005348;

            h4 {
                font-size: 3rem;
                padding: 0 20px;
            }

            tbody {
                width: 70%;
                margin-left: 20%;

                tr {
                    display: flex;
                    justify-content: space-between;
                }
            }
        }

        button {
            height: 65px;
            font-size: 3rem;
            border-radius: 10px;
            border: none;
            margin: 0.5rem 1rem;
            background-color: #0DD98B;
            border-top: none;
            border-left: none;
            border-bottom: 3px solid #005348;
            border-right: 3px solid #005348;

            transition: all 0.5s;
        }
        button:hover {
            background-color: #0feb96;
            cursor: pointer;
        }
    }

    .slide-left-leave-active,
    .slide-right-leave-active {
        transition: all .5s linear;
    }
    .slide-left-leave-to {
        transform: translateX(-100%);
    }
    .slide-right-leave-to {
        transform: translateX(100%);
    }

    @media screen and (max-width: 1200px) {
        .select-ingredients {
            flex-direction: column;
            padding: 50px 5%;
        }
        .ingredient-list {
            width: 100%;

            button {
                font-size: 2rem;
            }
        }
        .ingredient-search {
            padding-bottom: 2%;

            input {
                width: 100%;
            }
        }
    }
</style>