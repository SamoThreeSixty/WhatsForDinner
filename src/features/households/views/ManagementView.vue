<script setup lang="ts">
import {computed, onMounted, ref} from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import UiLabel from '@/components/ui/Label.vue';
import UiSelect from '@/components/ui/Select.vue';
import {useHouseholdStore} from '@/stores/household';
import {getApiErrorMessage} from '@/lib/errors';
import {
    approveMembership,
    getHouseholdManagement,
    rejectMembership,
    removeMembership,
    sendAccessInvite,
    setHouseholdOpenMembership,
} from '@/features/households/services/household-management.service';
import type {HouseholdAccessInvite, HouseholdMember} from '@/features/households/types/management';

const householdStore = useHouseholdStore();

const loading = ref(false);
const error = ref('');
const message = ref('');
const inviteEmail = ref('');
const inviteName = ref('');
const sendingInvite = ref(false);
const togglingOpen = ref(false);

const members = ref<HouseholdMember[]>([]);
const pending = ref<HouseholdMember[]>([]);
const accesses = ref<HouseholdAccessInvite[]>([]);

const activeHousehold = computed(() => householdStore.activeHousehold);
const openMembershipMode = computed({
    get: () => (activeHousehold.value?.new_members ? 'open' : 'closed'),
    set: (value: string) => {
        onToggleOpen(value === 'open');
    },
});

async function loadManagement() {
    if (!householdStore.activeHouseholdId) {
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const response = await getHouseholdManagement(householdStore.activeHouseholdId);
        members.value = response.members;
        pending.value = response.pending;
        accesses.value = response.accesses;

        const idx = householdStore.households.findIndex((item) => item.id === response.household.id);
        if (idx >= 0) {
            householdStore.households[idx] = response.household;
        }
    } catch (e: unknown) {
        error.value = getApiErrorMessage(e, 'Unable to load household management.');
    } finally {
        loading.value = false;
    }
}

async function onSendInvite() {
    if (!householdStore.activeHouseholdId || !inviteEmail.value.trim() || sendingInvite.value) {
        return;
    }

    sendingInvite.value = true;
    error.value = '';
    message.value = '';

    try {
        const response = await sendAccessInvite(householdStore.activeHouseholdId, {
            email: inviteEmail.value.trim(),
            name: inviteName.value.trim() || undefined,
        });
        message.value = response.message;
        inviteEmail.value = '';
        inviteName.value = '';
        await loadManagement();
    } catch (e: unknown) {
        error.value = getApiErrorMessage(e, 'Unable to send access email.');
    } finally {
        sendingInvite.value = false;
    }
}

async function onToggleOpen(value: boolean) {
    if (!householdStore.activeHouseholdId || togglingOpen.value) {
        return;
    }

    togglingOpen.value = true;
    error.value = '';

    try {
        const response = await setHouseholdOpenMembership(householdStore.activeHouseholdId, value);
        message.value = response.message;

        if (activeHousehold.value) {
            activeHousehold.value.new_members = value;
        }
    } catch (e: unknown) {
        error.value = getApiErrorMessage(e, 'Unable to change household access mode.');
    } finally {
        togglingOpen.value = false;
    }
}

async function onApprove(membershipId: number) {
    error.value = '';

    try {
        await approveMembership(membershipId);
        await loadManagement();
    } catch (e: unknown) {
        error.value = getApiErrorMessage(e, 'Unable to approve member.');
    }
}

async function onReject(membershipId: number) {
    error.value = '';

    try {
        await rejectMembership(membershipId);
        await loadManagement();
    } catch (e: unknown) {
        error.value = getApiErrorMessage(e, 'Unable to reject member.');
    }
}

async function onRemove(membershipId: number) {
    error.value = '';

    try {
        await removeMembership(membershipId);
        await loadManagement();
    } catch (e: unknown) {
        error.value = getApiErrorMessage(e, 'Unable to remove member.');
    }
}

onMounted(async () => {
    await householdStore.fetchMyHouseholds();
    await loadManagement();
});
</script>

<template>
    <section class="min-w-0 space-y-5">
        <PageHeader
            eyebrow="Household"
            title="Manage household access"
            subtitle="Invite members, approve join requests, and control whether your household accepts new requests."
        />

        <p v-if="message" class="rounded-lg border border-emerald-700/25 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800">{{ message }}</p>
        <p v-if="error" class="rounded-lg border border-rose-700/25 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-800">{{ error }}</p>

        <section class="rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.11em] text-emerald-800/85">Household details</h2>
            <p class="mt-2 text-sm text-emerald-900"><strong>Name:</strong> {{ activeHousehold?.name || '—' }}</p>
            <p class="mt-1 text-sm break-all text-emerald-900"><strong>Slug:</strong> {{ activeHousehold?.slug || '—' }}</p>
            <div class="mt-3 max-w-sm">
                <UiLabel for="open-membership-mode">Accepting new requests</UiLabel>
                <UiSelect id="open-membership-mode" v-model="openMembershipMode" :disabled="togglingOpen">
                    <option value="open">Open to new members</option>
                    <option value="closed">Closed to new members</option>
                </UiSelect>
            </div>
        </section>

        <section class="rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.11em] text-emerald-800/85">Invite by email</h2>
            <form class="mt-3 grid gap-2 sm:grid-cols-2" @submit.prevent="onSendInvite">
                <input v-model="inviteName" type="text" placeholder="Name (optional)" class="rounded-xl border border-emerald-900/20 px-3 py-2 text-sm" />
                <input v-model="inviteEmail" type="email" placeholder="Email" class="rounded-xl border border-emerald-900/20 px-3 py-2 text-sm" required />
                <button type="submit" class="sm:col-span-2 rounded-xl border border-emerald-700 bg-emerald-700 px-4 py-2 text-sm font-semibold text-white" :disabled="sendingInvite || !inviteEmail.trim()">
                    {{ sendingInvite ? 'Sending...' : 'Send access email' }}
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.11em] text-emerald-800/85">Pending approvals</h2>
            <p v-if="!loading && pending.length === 0" class="mt-2 text-sm text-emerald-900/70">No pending join requests.</p>
            <ul class="mt-3 grid gap-2">
                <li v-for="membership in pending" :key="membership.id" class="rounded-xl border border-emerald-900/12 bg-emerald-50/35 px-3 py-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">{{ membership.user.name }}</p>
                            <p class="text-xs text-emerald-800/80">{{ membership.user.email }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button class="rounded-lg border px-2.5 py-1 text-xs font-semibold" @click="onApprove(membership.id)">Approve</button>
                            <button class="rounded-lg border px-2.5 py-1 text-xs font-semibold" @click="onReject(membership.id)">Deny</button>
                        </div>
                    </div>
                </li>
            </ul>
        </section>

        <section class="rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.11em] text-emerald-800/85">Current members</h2>
            <p v-if="!loading && members.length === 0" class="mt-2 text-sm text-emerald-900/70">No approved members yet.</p>
            <ul class="mt-3 grid gap-2">
                <li v-for="membership in members" :key="membership.id" class="rounded-xl border border-emerald-900/12 bg-emerald-50/35 px-3 py-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-emerald-900">{{ membership.user.name }} <span class="text-xs text-emerald-700">({{ membership.role }})</span></p>
                            <p class="text-xs text-emerald-800/80">{{ membership.user.email }}</p>
                        </div>
                        <button class="rounded-lg border px-2.5 py-1 text-xs font-semibold" @click="onRemove(membership.id)">Remove</button>
                    </div>
                </li>
            </ul>
        </section>

        <section class="rounded-2xl border border-emerald-900/15 bg-white/80 p-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.11em] text-emerald-800/85">Pending email invites</h2>
            <p v-if="!loading && accesses.length === 0" class="mt-2 text-sm text-emerald-900/70">No pending invites.</p>
            <ul class="mt-3 grid gap-2">
                <li v-for="invite in accesses" :key="invite.id" class="rounded-xl border border-emerald-900/12 bg-emerald-50/35 px-3 py-2.5">
                    <p class="text-sm font-semibold text-emerald-900">{{ invite.name || 'Invitee' }}</p>
                    <p class="text-xs text-emerald-800/80">{{ invite.email }}</p>
                </li>
            </ul>
        </section>
    </section>
</template>
