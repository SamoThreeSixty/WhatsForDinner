import {createRouter, createWebHistory} from 'vue-router';

const routes = [
    // region Auth
    {
        path: '/',
        component: () => import('../layout/AuthLayout.vue'),
        children: [
            {
                path: '',
                redirect: '/login'
            },
            {
                path: 'login',
                name: 'auth.login',
                component: () => import('../features/auth/views/Login.vue')
            },
            {
                path: 'register',
                name: 'auth.register',
                component: () => import('../features/auth/views/Register.vue')
            },
            {
                path: 'forgot-password',
                name: 'auth.forgot_password',
                component: () => import('../features/auth/views/ForgottenPassword.vue')
            },
            {
                path: 'reset-password/:token',
                name: 'auth.reset_password',
                component: () => import('../features/auth/views/ResetPassword.vue'),
                props: true
            }
        ],
    },
    // endregion


    // {
    //     path: '/home',
    //     name: 'Home',
    //     component: () => import('../vue/pages/home.vue'),
    //     meta: {
    //         requiresAuth: true,
    //         requiresVerified: true,
    //         tab: 'home'
    //     }
    // },
    // {
    //     path: '/user',
    //     name: 'User',
    //     component: () => import('../vue/pages/user.vue'),
    //     meta: {
    //         requiresAuth: true,
    //         requiresVerified: true,
    //         tab: 'account'
    //     }
    // },
    // {
    //     path: '/generate',
    //     name: 'Generate',
    //     component: () => import('../vue/pages/generate.vue'),
    //     meta: {
    //         requiresAuth: true,
    //         requiresVerified: true,
    //         tab: 'pantry'
    //     }
    // },
    // {
    //     path: '/results',
    //     name: 'Results',
    //     component: () => import('../vue/pages/results.vue'),
    //     meta: {
    //         requiresAuth: true,
    //         requiresVerified: true,
    //         tab: 'recipes'
    //     }
    // },

    // Catchall to redirect to /login
    {
        path: '/:pathMatch(.*)*',
        redirect: '/login'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
