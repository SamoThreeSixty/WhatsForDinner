import axios from "axios";

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;

export interface ApiErrorResponse {
    message?: string;
    errors?: Record<string, string[]>;
}

export interface ResourceCollectionResponse<T> {
    data: T[];
}

export const api = axios.create({
    baseURL: `${apiBaseUrl}/api`,
    headers: {
        'Content-Type': 'application/json',
    },
    withCredentials: true,
})

export function saveActiveHousehold(householdId: number | null) {
    if (householdId === null) {
        window.localStorage.removeItem('active_household_id');
        return;
    }

    window.localStorage.setItem('active_household_id', String(householdId));
}

api.interceptors.request.use((config) => {
    const householdId = window.localStorage.getItem('active_household_id');

    if (householdId) {
        config.headers['X-Household-Id'] = householdId;
    }

    return config;
});
