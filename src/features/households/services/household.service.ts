import {api} from '@/lib/api';
import type {Household, MyHouseholdsResponse} from '@/features/households/types/household';

export async function listMyHouseholds() {
    const response = await api.get<MyHouseholdsResponse>('/households/my');
    return response.data;
}

export async function setActiveHousehold(householdId: number) {
    const response = await api.post<{ message: string; active_household_id: number }>('/households/active', {
        household_id: householdId,
    });

    return response.data;
}

export async function requestHouseholdJoin(joinCode: string) {
    const response = await api.post<{ message: string }>('/households/join-requests', {
        join_code: joinCode,
    });

    return response.data;
}

export async function createHousehold(name: string) {
    const response = await api.post<{ message: string; household: Household }>('/households', {
        name,
    });

    return response.data;
}
