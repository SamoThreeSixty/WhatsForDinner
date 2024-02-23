<script setup>
    import { ref, onMounted } from 'vue';
    import { useItemsStore } from '../../stores/items.js';
    import Results from '../components/Results.vue';
    import axios from 'axios';

    const itemsStore = useItemsStore();
    const results = ref([])

    let loading = ref(true)

    onMounted(() => {
        get_recipies()
    })

    const get_recipies = async () => {
        await axios.post('/api/get_recipies', itemsStore.items).then(result => {
            console.log(result)
            console.log(typeof(result))
            if(Array.isArray(result)) {
                result.data.map((each) => {
                    results.value.push(each)
                })
            } else {
                for(let i = 0; i < result.data.length; i++) {
                    results.value.push(result.data[i])
                }
            }
            
            loading.value = false;
        })
    }
    
</script>

<template>
    <Transition name="loading">
        <div class="loading-pannel" v-show="loading">
        
        </div>
    </Transition>
    <router-link to="/">Back to start</router-link>
    <div class="results">
        <h2>Here are some options...</h2>
        <h3>Who's Hungry</h3>

        <Results :results="results" />      
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
    .loading-pannel {
        position: absolute;
        width: 100lvw;
        height: 100lvh;
        
        background-color: #96EE77;
    }
    .loading-leave-active {
        transition: all 0.5s ease-out;
    }
    .loading-leave-to {
        transform: translateY(-100%);
    }
    .results {
        margin: 0 auto;
        padding-top: 50px;


        h2 {
            text-align: center;
            padding-top: 3rem;
            font-size: 3rem;
        }
        h3 {
            text-align: center;
            padding-top: 0.5rem;
            font-size: 2rem;
        }
        
    }  
        
</style>