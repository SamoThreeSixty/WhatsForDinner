<script setup lang="ts">
import {computed, onMounted, ref} from 'vue';
import {useRoute, useRouter} from 'vue-router';
import Card from '@/features/auth/components/Card.vue';
import Title from '@/features/auth/components/Title.vue';
import UiLabel from '@/components/ui/Label.vue';
import UiButton from '@/components/ui/Button.vue';
import UiInput from '@/components/ui/Input.vue';
import {useHouseholdStore} from '@/stores/household';
import {redeemHouseholdInvite} from '@/features/households/services/household-management.service';
import {saveActiveHousehold} from '@/lib/api';
import {getApiErrorMessage} from '@/lib/errors';

const router = useRouter();
const route = useRoute();
const householdStore = useHouseholdStore();

const slug = ref('');
const inviteToken = ref('');
const requesting = ref(false);
const redeeming = ref(false);

const statusError = computed(() => householdStore.error);
const statusMessage = computed(() => householdStore.message);

onMounted(() => {
    const token = String(route.query.invite_token || '');
    if (token) {
        inviteToken.value = token;
    }
});

async function requestToJoinHousehold() {
    if (!slug.value.trim() || requesting.value) {
        return;
    }

    requesting.value = true;

    try {
        await householdStore.requestJoin(slug.value);
        slug.value = '';
    } finally {
        requesting.value = false;
    }
}

async function acceptInvite() {
    if (!inviteToken.value || redeeming.value) {
        return;
    }

    redeeming.value = true;
    householdStore.error = '';
    householdStore.message = '';

    try {
        const result = await redeemHouseholdInvite(inviteToken.value);
        householdStore.message = result.message;
        householdStore.activeHouseholdId = result.active_household_id;
        saveActiveHousehold(result.active_household_id);
        await householdStore.fetchMyHouseholds();
        await router.push({name: 'app.dashboard'});
    } catch (e: unknown) {
        householdStore.error = getApiErrorMessage(e, 'Unable to accept invite.');
    } finally {
        redeeming.value = false;
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
            <h3 class="text-sm font-semibold uppercase tracking-[0.08em] text-emerald-800/90">Join by household slug</h3>

            <p v-if="inviteToken" class="mt-2 rounded-lg border border-emerald-700/25 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                Invite token detected. You can accept your email invite directly.
            </p>

            <UiButton v-if="inviteToken" class="mt-3 w-full" type="button" :disabled="redeeming" @click="acceptInvite">
                {{ redeeming ? 'Accepting...' : 'Accept invite and join' }}
            </UiButton>

            <form class="mt-3 grid gap-2" @submit.prevent="requestToJoinHousehold">
                <UiLabel for="join-code">Household slug</UiLabel>
                <UiInput
                    id="join-code"
                    v-model="slug"
                    class="!mb-0 tracking-[0.02em]"
                    type="text"
                    autocomplete="off"
                    placeholder="Paste household slug"
                    required
                />

                <UiButton class="mt-2" type="submit" :disabled="requesting || !slug.trim()">
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
