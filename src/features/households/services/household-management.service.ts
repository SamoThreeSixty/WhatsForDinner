import {api} from '@/lib/api';
import type {HouseholdManagementResponse} from '@/features/households/types/management';

export async function getHouseholdManagement(householdId: number) {
    const response = await api.get<HouseholdManagementResponse>(`/households/${householdId}/management`);
    return response.data;
}

export async function sendAccessInvite(householdId: number, payload: { email: string; name?: string }) {
    const response = await api.post<{ message: string }>(`/households/${householdId}/accesses`, payload);
    return response.data;
}

export async function setHouseholdOpenMembership(householdId: number, openToNewMembers: boolean) {
    const response = await api.patch<{ message: string }>(`/households/${householdId}/open-membership`, {
        new_members: openToNewMembers,
    });
    return response.data;
}

export async function approveMembership(membershipId: number) {
    const response = await api.post<{ message: string }>(`/household-memberships/${membershipId}/approve`);
    return response.data;
}

export async function rejectMembership(membershipId: number) {
    const response = await api.post<{ message: string }>(`/household-memberships/${membershipId}/reject`);
    return response.data;
}

export async function removeMembership(membershipId: number) {
    const response = await api.delete<{ message: string }>(`/household-memberships/${membershipId}`);
    return response.data;
}

export async function redeemHouseholdInvite(token: string) {
    const response = await api.post<{ message: string; active_household_id: number }>('/household-access/redeem', {
        token,
    });
    return response.data;
}
