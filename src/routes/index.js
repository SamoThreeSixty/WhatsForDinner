import {createRouter, createWebHistory} from 'vue-router';
import {useAuthStore} from '@/stores/auth';

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

    // region Application
    {
        path: '/',
        component: () => import('../layout/AppLayout.vue'),
        meta: {
            requiresAuth: true,
            requiresVerified: true,
        },
        children: [
            {
                path: 'dashboard',
                name: 'app.dashboard',
                component: () => import('../features/dashboard/views/DashboardHome.vue'),
            },
            {
                path: 'ingredients',
                name: 'app.ingredients',
                component: () => import('../features/dashboard/views/IngredientsView.vue'),
            },
            {
                path: 'recipes',
                name: 'app.recipes',
                component: () => import('../features/dashboard/views/RecipesView.vue'),
            },
            {
                path: 'shopping',
                name: 'app.shopping',
                component: () => import('../features/dashboard/views/ShoppingListView.vue'),
            },
            {
                path: 'calendar',
                name: 'app.calendar',
                component: () => import('../features/dashboard/views/CalendarView.vue'),
            },
            {
                path: 'account',
                name: 'app.account',
                component: () => import('../features/dashboard/views/AccountView.vue'),
            },
            {
                path: 'settings',
                name: 'app.settings',
                component: () => import('../features/dashboard/views/SettingsView.vue'),
            }
        ]
    },
    // endregion

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

const guestRouteNames = new Set([
    'auth.login',
    'auth.register',
    'auth.forgot_password',
    'auth.reset_password',
]);

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authStore.hasCheckedSession) {
        await authStore.verify();
    }

    if (to.meta.requiresAuth && !authStore.isLoggedIn) {
        return {name: 'auth.login'};
    }

    if (to.meta.requiresVerified && !authStore.isVerified) {
        return {name: 'auth.login'};
    }

    if (to.name && guestRouteNames.has(String(to.name)) && authStore.isLoggedIn && authStore.isVerified) {
        return {name: 'app.dashboard'};
    }
});

export default router;
