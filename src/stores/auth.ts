import {defineStore} from "pinia";
import * as authService from '@/features/auth/services/auth.service';
import type {LoginForm} from '@/features/auth/types/login-form';
import type {RegisterForm} from '@/features/auth/types/register-form';
import type {AuthUser} from "@/features/auth/types/auth-user.ts";
import axios from "axios";
import {useHouseholdStore} from '@/stores/household';
import {getApiErrorMessage} from "@/lib/errors.ts";

export interface AuthState {
    isLoggedIn: boolean;
    isVerified: boolean;
    hasCheckedSession: boolean;
    userId: number | null;
    username: string;
    email: string;
    user: AuthUser | null;
    authError: string;
    authMessage: string;
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        isLoggedIn: false,
        isVerified: false,
        hasCheckedSession: false,
        userId: null,
        username: '',
        email: '',
        user: null,
        authError: '',
        authMessage: '',
    }),
    actions: {
        clearAuthState() {
            const householdStore = useHouseholdStore();

            this.isLoggedIn = false;
            this.isVerified = false;
            this.userId = null;
            this.username = '';
            this.email = '';
            this.user = null;
            this.authError = '';
            this.authMessage = '';

            householdStore.clear();
        },
        setUser(user: AuthUser) {
            this.isLoggedIn = true;
            this.isVerified = Boolean(user.email_verified_at);
            this.hasCheckedSession = true;
            this.userId = user.id;
            this.username = user.name;
            this.email = user.email;
            this.user = user;
        },
        async login(payload: LoginForm) {
            this.authError = '';
            this.authMessage = '';

            try {
                const result = await authService.login(payload);
                this.setUser(result.user);
                this.authMessage = result.message;
            } catch (error: unknown) {
                this.clearAuthState();
                this.authError = getApiErrorMessage(error, 'Unable to authenticate.');
            }
        },
        async register(payload: RegisterForm) {
            this.authError = '';
            this.authMessage = '';

            try {
                const result = await authService.register(payload);
                this.setUser(result.user);
                this.authMessage = result.message;
            } catch (error: unknown) {
                this.clearAuthState();
                this.authError = getApiErrorMessage(error, 'Unable to create account.');
            }
        },
        async logout() {
            this.authError = '';
            let message = '';

            try {
                const result = await authService.logout();
                message = result.message;
            } finally {
                this.clearAuthState();
                this.authMessage = message;
            }
        },
        async verify() {
            this.authError = '';

            try {
                const result = await authService.verify();
                this.setUser(result.user);
            } catch (error: unknown) {
                this.clearAuthState();
                this.hasCheckedSession = true;

                if (axios.isAxiosError(error) && error.response?.status === 401) {
                    return;
                }

                this.authError = 'Unable to verify current session.';
            }
        }
    }
});
