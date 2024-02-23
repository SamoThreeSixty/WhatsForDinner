<script setup>
    import Background from '../components/Background.vue';
    import { useRouter } from 'vue-router';

    const router = useRouter();

</script>
<template>
    <div class="section">
        <div class="section-buttons">
            <button @click="router.push('/user')">My Recipies</button>
            <button @click="router.push('/generate')">Generate</button>   
        </div>
    </div>
    
    <Background />
</template>
<style scoped>
    body {
        max-height: 100lvh;
        max-width: 100lvw;
        overflow: hidden;
    }
    .section {
        width: 100%;
        height: 100%;

        display: flex;
        align-items: center;
        .section-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-around;
            align-content: center;

            button {
                min-height: 65px;
                padding: 10px 50px 10px 50px;
                margin: 0 20px;
                background-color: #0DD98B;
                border-radius: 20px;
                font-size: 2.5rem;
                border-top: none;
                border-left: none;
                border-bottom: 3px solid #005348;
                border-right: 3px solid #005348;

                z-index: 100;
                transition: all .5s;
            }
            button:nth-child(1){
                margin-left: 2.5rem;
            }
            button:hover {
                background-color: #0feb96;
                cursor: pointer;
            }
        }
    }
</style>