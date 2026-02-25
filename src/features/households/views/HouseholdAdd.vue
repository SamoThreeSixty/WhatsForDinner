<script setup lang="ts">
import {computed, ref} from 'vue';
import {useRouter} from 'vue-router';
import Card from '@/features/auth/components/Card.vue';
import Title from '@/features/auth/components/Title.vue';
import UiLabel from '@/components/ui/Label.vue';
import UiButton from '@/components/ui/Button.vue';
import UiInput from '@/components/ui/Input.vue';
import {useHouseholdStore} from '@/stores/household';

const router = useRouter();
const householdStore = useHouseholdStore();

const name = ref('');
const creating = ref(false);

const statusError = computed(() => householdStore.error);
const statusMessage = computed(() => householdStore.message);

async function createHouseholdAndContinue() {
    if (!name.value.trim() || creating.value) {
        return;
    }

    creating.value = true;

    try {
        await householdStore.createHousehold(name.value);
        await router.push({name: 'app.dashboard'});
    } finally {
        creating.value = false;
    }
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

        <section class="rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h3 class="text-sm font-semibold uppercase tracking-[0.08em] text-emerald-800/90">Create household</h3>

            <form class="mt-3 grid gap-2" @submit.prevent="createHouseholdAndContinue">
                <UiLabel for="create-household-name">Household name</UiLabel>
                <UiInput
                    id="create-household-name"
                    v-model="name"
                    class="!mb-0"
                    type="text"
                    autocomplete="off"
                    placeholder="e.g. Bradshaw Home"
                    required
                />

                <UiButton class="mt-2" type="submit" :disabled="creating || !name.trim()">
                    {{ creating ? 'Creating...' : 'Create household' }}
                </UiButton>

                <button
                    type="button"
                    class="mt-1 text-sm font-semibold text-emerald-800 hover:underline"
                    @click="router.push({name: 'auth.access'})"
                >
                    Back to household access
                </button>
            </form>
        </section>
    </Card>
</template>
