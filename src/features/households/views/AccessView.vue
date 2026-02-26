<script setup lang="ts">
import {computed, onMounted, ref} from 'vue';
import {useRouter} from 'vue-router';
import Card from '@/features/auth/components/Card.vue';
import Title from '@/features/auth/components/Title.vue';
import UiButton from '@/components/ui/Button.vue';
import {useHouseholdStore} from '@/stores/household';
import {useAuthStore} from '@/stores/auth';

const router = useRouter();
const householdStore = useHouseholdStore();
const authStore = useAuthStore();

const selectedHouseholdId = ref<number | null>(null);
const selecting = ref(false);

const households = computed(() => householdStore.households);
const statusError = computed(() => householdStore.error);
const statusMessage = computed(() => householdStore.message);
const hasHouseholds = computed(() => households.value.length > 0);
const hasMultipleHouseholds = computed(() => households.value.length > 1);

onMounted(async () => {
    await householdStore.fetchMyHouseholds();

    if (householdStore.activeHouseholdId !== null) {
        selectedHouseholdId.value = householdStore.activeHouseholdId;
        return;
    }

    if (householdStore.households.length > 0) {
        selectedHouseholdId.value = householdStore.households[0].id;
    }
});

async function continueToDashboard() {
    await router.push({name: 'app.dashboard'});
}

async function selectHouseholdAndContinue() {
    if (selectedHouseholdId.value === null || selecting.value) {
        return;
    }

    selecting.value = true;

    try {
        await householdStore.setActiveHousehold(selectedHouseholdId.value);
        await continueToDashboard();
    } finally {
        selecting.value = false;
    }
}

async function logoutToLogin() {
    await authStore.logout();
    await router.push({name: 'auth.login'});
}
</script>

<template>
    <Card>
        <Title />

        <p v-if="statusMessage" class="mb-3 rounded-lg border border-emerald-700/25 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">
            {{ statusMessage }}
        </p>

        <p v-if="statusError" class="mb-3 rounded-lg border border-rose-700/25 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-800">
            {{ statusError }}
        </p>

        <section class="mb-5 rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h3 class="text-sm font-semibold uppercase tracking-[0.08em] text-emerald-800/90">
                {{ hasHouseholds ? (hasMultipleHouseholds ? 'Choose household' : 'Your household') : 'No household yet' }}
            </h3>

            <template v-if="hasHouseholds">
                <ul class="mt-3 grid gap-2">
                    <li v-for="household in households" :key="household.id">
                        <label class="flex cursor-pointer items-center justify-between rounded-xl border border-emerald-900/12 bg-emerald-50/35 px-3 py-2.5">
                            <span class="text-sm font-semibold text-emerald-900">{{ household.name }}</span>
                            <input
                                v-model.number="selectedHouseholdId"
                                type="radio"
                                name="active-household"
                                :value="household.id"
                            >
                        </label>
                    </li>
                </ul>

                <UiButton
                    class="mt-4 w-full"
                    type="button"
                    :disabled="selecting || selectedHouseholdId === null"
                    @click="selectHouseholdAndContinue"
                >
                    {{ selecting ? 'Switching...' : 'Continue' }}
                </UiButton>
            </template>

            <p v-else class="mt-3 text-sm text-[var(--green-muted)]">
                You are not assigned to a household yet. Create one to continue.
            </p>
        </section>

        <section class="mt-5 rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h3 class="text-sm font-semibold uppercase tracking-[0.08em] text-emerald-800/90">Get access</h3>

            <div class="mt-3 grid gap-2">
                <UiButton class="w-full" type="button" @click="router.push({name: 'auth.household_join'})">
                    Join by code
                </UiButton>

                <UiButton class="w-full" type="button" @click="router.push({name: 'auth.household_add'})">
                    Create household
                </UiButton>
            </div>
        </section>

        <div class="route-links">
            <button type="button" class="auth-link-btn" @click="logoutToLogin">Logout and go to login</button>
        </div>
    </Card>
</template>
