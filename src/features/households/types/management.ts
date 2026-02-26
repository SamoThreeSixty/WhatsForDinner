import type {Household} from '@/features/households/types/household';

export interface HouseholdMember {
    id: number;
    role: string;
    status: string;
    approved_at: string | null;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

export interface HouseholdAccessInvite {
    id: number;
    name: string | null;
    email: string;
    status: string;
    created_at: string;
    expires_at: string | null;
}

export interface HouseholdManagementResponse {
    household: Household;
    members: HouseholdMember[];
    pending: HouseholdMember[];
    accesses: HouseholdAccessInvite[];
}
