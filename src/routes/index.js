import {createRouter, createWebHistory} from 'vue-router';
import {useAuthStore} from '@/stores/auth';
import {useHouseholdStore} from '@/stores/household';

const routes = [
    // region Auth
    {
        path: '/',
        component: () => import('../layout/AuthLayout.vue'),
        meta: {
            requiresHousehold: false
        },
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
            },
            {
                path: 'access',
                name: 'auth.access',
                component: () => import('../features/households/views/AccessView.vue'),
                meta: {
                    requiresAuth: true,
                    requiresVerified: true,
                },
            },
            {
                path: 'access/add',
                name: 'auth.household_add',
                component: () => import('../features/households/views/HouseholdAdd.vue'),
                meta: {
                    requiresAuth: true,
                    requiresVerified: true,
                },
            },
            {
                path: 'access/join',
                name: 'auth.household_join',
                component: () => import('../features/households/views/HouseholdJoin.vue'),
                meta: {
                    requiresAuth: true,
                    requiresVerified: true,
                },
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
                component: () => import('../features/dashboard/views/DashboardView.vue'),
            },

            // region TODO: Other features
            {
                path: 'ingredients',
                name: 'app.ingredients',
                component: () => import('../features/dashboard/views/IngredientsView.vue'),
            },
            {
                path: 'household/manage',
                name: 'app.household_manage',
                component: () => import('../features/households/views/ManagementView.vue'),
            },
            // {
            //     path: 'recipes',
            //     name: 'app.recipes',
            //     component: () => import('../features/dashboard/views/RecipesView.vue'),
            // },
            // {
            //     path: 'shopping',
            //     name: 'app.shopping',
            //     component: () => import('../features/dashboard/views/ShoppingListView.vue'),
            // },
            // {
            //     path: 'calendar',
            //     name: 'app.calendar',
            //     component: () => import('../features/dashboard/views/CalendarView.vue'),
            // },
            // {
            //     path: 'account',
            //     name: 'app.account',
            //     component: () => import('../features/dashboard/views/AccountView.vue'),
            // },
            // {
            //     path: 'settings',
            //     name: 'app.settings',
            //     component: () => import('../features/dashboard/views/SettingsView.vue'),
            // }
            // endregion
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
    if (to.name === 'auth.logout') {
        return;
    }

    const authStore = useAuthStore();
    const householdStore = useHouseholdStore();

    if (!authStore.hasCheckedSession) {
        await authStore.verify();
    }

    if (to.meta.requiresAuth && !authStore.isLoggedIn) {
        return {name: 'auth.login'};
    }

    if (to.meta.requiresVerified && !authStore.isVerified) {
        return {name: 'auth.login'};
    }

    if (authStore.isLoggedIn && authStore.isVerified) {
        await householdStore.ensureActiveHousehold();

        const routeRequiresHousehold = to.meta.requiresHousehold ?? false;
        const needsHouseholdGate = !householdStore.activeHouseholdId;

        if (needsHouseholdGate && to.name !== 'auth.access' && to.name !== 'auth.household_add' && to.name !== 'auth.household_join') {
            return {name: 'auth.access'};
        }

        if (!needsHouseholdGate && (to.name === 'auth.access' || to.name === 'auth.household_add' || to.name === 'auth.household_join')) {
            return {name: 'app.dashboard'};
        }

        if (routeRequiresHousehold && needsHouseholdGate) {
            return {name: 'auth.access'};
        }
    }

    if (to.name && guestRouteNames.has(String(to.name)) && authStore.isLoggedIn && authStore.isVerified) {
        if (!householdStore.hasCheckedHouseholds) {
            await householdStore.ensureActiveHousehold();
        }

        return householdStore.activeHouseholdId
            ? {name: 'app.dashboard'}
            : {name: 'auth.access'};
    }
});

export default router;
