<script setup lang="ts">
import {computed} from 'vue';
import {useAuthStore} from '@/stores/auth';
import QuickAccessCard from '@/features/dashboard/components/QuickAccessCard.vue';
import PageHeader from '@/components/PageHeader.vue';

const authStore = useAuthStore();

const firstName = computed(() => authStore.username?.split(' ')[0] || 'there');

const quickActions = [
    {
        title: 'Household larder',
        description: 'Track dairy, meats, grains, produce, and seasonings in one place.',
        routeName: 'app.ingredients',
        icon: 'Larder'
    },
    {
        title: 'Recipes saved and generate',
        description: 'Keep family favorites and generate meals from available ingredients.',
        routeName: 'app.recipes',
        icon: 'Recipes'
    },
    {
        title: 'Shopping list',
        description: 'Auto-build lists from your weekly plan and what is currently missing.',
        routeName: 'app.shopping',
        icon: 'List'
    },
    {
        title: 'Calendar',
        description: 'Assign meals by day so the whole household can follow the same plan.',
        routeName: 'app.calendar',
        icon: 'Planner'
    }
];
</script>

<template>
    <section class="min-w-0 space-y-5">
        <PageHeader
            eyebrow="Overview"
            :title="`Welcome back, ${firstName}`"
            subtitle="Keep your home kitchen in sync: update ingredients, plan meals, generate recipes, and keep shopping tight."
        />

        <section class="min-w-0">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-[0.12em] text-emerald-800/80">Quick access</h2>
            <div class="grid min-w-0 gap-3 sm:grid-cols-2">
                <QuickAccessCard
                    v-for="action in quickActions"
                    :key="action.routeName"
                    :title="action.title"
                    :description="action.description"
                    :route-name="action.routeName"
                    :icon="action.icon"
                />
            </div>
        </section>
    </section>
</template>
