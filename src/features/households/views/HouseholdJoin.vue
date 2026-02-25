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

const joinCode = ref('');
const requesting = ref(false);

const statusError = computed(() => householdStore.error);
const statusMessage = computed(() => householdStore.message);

async function requestToJoinHousehold() {
    if (!joinCode.value.trim() || requesting.value) {
        return;
    }

    requesting.value = true;

    try {
        await householdStore.requestJoin(joinCode.value);
        joinCode.value = '';
    } finally {
        requesting.value = false;
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
            <h3 class="text-sm font-semibold uppercase tracking-[0.08em] text-emerald-800/90">Join by code</h3>

            <form class="mt-3 grid gap-2" @submit.prevent="requestToJoinHousehold">
                <UiLabel for="join-code">Join code</UiLabel>
                <UiInput
                    id="join-code"
                    v-model="joinCode"
                    class="!mb-0 uppercase tracking-[0.08em]"
                    type="text"
                    autocomplete="off"
                    placeholder="Enter join code"
                    required
                />

                <UiButton class="mt-2" type="submit" :disabled="requesting || !joinCode.trim()">
                    {{ requesting ? 'Submitting...' : 'Request access' }}
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
