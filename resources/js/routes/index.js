import { createRouter, createWebHistory } from "vue-router";

const routes = [
    {
        path: '/',
        name: 'Home',
        component: () => import('../vue/pages/home.vue')
    },
    {
        path: '/user',
        name: 'User',
        component: () => import('../vue/pages/user.vue')
    },
    {
        path: '/generate',
        name: 'Generate',
        component: () => import('../vue/pages/generate.vue')
    },
    {
        path: '/results',
        name: 'Results',
        component: () => import('../vue/pages/results.vue')
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router;