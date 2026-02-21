<template>
    <router-view>
    </router-view>
</template>

<style>
    :root {
        --accent: #0dd98b;
        --accent-strong: #00b59c;
        --accent-deep: #005348;
        --surface: #f2f7f4;
        --ink: #123427;
        --shadow-soft: 0 10px 25px rgba(0, 0, 0, 0.12);
        --shadow-strong: 0 18px 40px rgba(0, 0, 0, 0.18);
    }
    * {
        margin: 0;
        padding: 0;
        font-family: "Jost", sans-serif;
    }
    html {
        background: radial-gradient(circle at top, #b4f4a5 0%, #96ee77 45%, #7ed56a 100%);
        color: var(--ink);
    }
    body {
        min-height: 100lvh;
    }
    #app {
        height: 100lvh;
        position: relative;
    }
    
</style>
