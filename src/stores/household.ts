import {defineStore} from 'pinia';
import type {Household} from '@/features/households/types/household';
import * as householdService from '@/features/households/services/household.service';
import {saveActiveHousehold} from "@/lib/api.ts";
import {getApiErrorMessage} from "@/lib/errors.ts";

export interface HouseholdState {
    households: Household[];
    activeHouseholdId: number | null;
    hasCheckedHouseholds: boolean;
    loading: boolean;
    error: string;
    message: string;
}

export const useHouseholdStore = defineStore('household', {
    state: (): HouseholdState => ({
        households: [],
        activeHouseholdId: null,
        hasCheckedHouseholds: false,
        loading: false,
        error: '',
        message: '',
    }),
    getters: {
        hasHouseholds: (state) => state.households.length > 0,
        hasMultipleHouseholds: (state) => state.households.length > 1,
        activeHousehold: (state) => state.households.find((household) => household.id === state.activeHouseholdId) ?? null,
    },
    actions: {
        clear() {
            this.households = [];
            this.activeHouseholdId = null;
            this.hasCheckedHouseholds = false;
            this.loading = false;
            this.error = '';
            this.message = '';
            saveActiveHousehold(null);
        },

        async fetchMyHouseholds() {
            this.loading = true;
            this.error = '';

            try {
                const result = await householdService.listMyHouseholds();
                this.households = result.households;
                this.activeHouseholdId = result.active_household_id;
                saveActiveHousehold(this.activeHouseholdId);
            } catch (error: unknown) {
                this.error = getApiErrorMessage(error, 'Unable to load households.');
                this.households = [];
                this.activeHouseholdId = null;
                saveActiveHousehold(null);
            } finally {
                this.loading = false;
                this.hasCheckedHouseholds = true;
            }
        },

        async setActiveHousehold(householdId: number) {
            this.error = '';
            this.message = '';

            try {
                const result = await householdService.setActiveHousehold(householdId);
                this.activeHouseholdId = result.active_household_id;
                this.message = result.message;
                saveActiveHousehold(this.activeHouseholdId);
            } catch (error: unknown) {
                this.error = getApiErrorMessage(error, 'Unable to switch households.');
                throw error;
            }
        },

        async requestJoin(joinCode: string) {
            this.error = '';
            this.message = '';

            try {
                const result = await householdService.requestHouseholdJoin(joinCode.trim().toUpperCase());
                this.message = result.message;
            } catch (error: unknown) {
                this.error = getApiErrorMessage(error, 'Unable to submit join request.');
                throw error;
            }
        },

        async createHousehold(name: string) {
            this.error = '';
            this.message = '';

            try {
                const result = await householdService.createHousehold(name.trim());
                this.message = result.message;
                this.activeHouseholdId = result.household.id;
                saveActiveHousehold(this.activeHouseholdId);
                await this.fetchMyHouseholds();
            } catch (error: unknown) {
                this.error = getApiErrorMessage(error, 'Unable to create household.');
                throw error;
            }
        },

        async ensureActiveHousehold() {
            if (!this.hasCheckedHouseholds) {
                await this.fetchMyHouseholds();
            }

            if (!this.hasHouseholds) {
                return;
            }

            if (this.activeHouseholdId !== null) {
                return;
            }

            const [singleHousehold] = this.households;
            if (this.households.length === 1 && singleHousehold) {
                try {
                    await this.setActiveHousehold(singleHousehold.id);
                } catch {
                    // Leave the state unchanged and let the household screen handle recovery.
                }
            }
        },
    },
});
