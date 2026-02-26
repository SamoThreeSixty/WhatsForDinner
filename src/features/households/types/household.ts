export interface Household {
    id: number;
    name: string;
    slug?: string | null;
    new_members?: boolean;
    locale?: string | null;
    currency?: string | null;
}

export interface MyHouseholdsResponse {
    active_household_id: number | null;
    households: Household[];
}
