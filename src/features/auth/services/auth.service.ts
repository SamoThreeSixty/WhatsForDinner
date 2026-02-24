import {api} from "@/lib/api";
import {http} from "@/lib/http";
import type {LoginForm} from "@/features/auth/types/login-form";
import type {RegisterForm} from "@/features/auth/types/register-form";
import type {AuthUser} from "@/features/auth/types/auth-user";
import type {ForgottenPasswordForm} from "@/features/auth/types/forgotten-password-form.ts";
import type {ResetPasswordForm} from "@/features/auth/types/reset-password-form.ts";

export interface AuthSessionResponse {
    message: string;
    user: AuthUser;
}

export interface VerifyResponse {
    authenticated: boolean;
    verified: boolean;
    user: AuthUser;
}

export interface ResetPasswordPayload extends ResetPasswordForm {
    token: string;
    email: string;
}

async function ensureCsrfCookie(): Promise<void> {
    await http.get('/sanctum/csrf-cookie', {
        withCredentials: true,
    });
}

export async function login(loginPayload: LoginForm) {
    await ensureCsrfCookie();
    const response = await api.post<AuthSessionResponse>('/auth/login', loginPayload);
    return response.data;
}

export async function register(registerPayload: RegisterForm) {
    await ensureCsrfCookie();
    const response = await api.post<AuthSessionResponse>('/auth/register', registerPayload);
    return response.data;
}

export async function logout() {
    await ensureCsrfCookie();
    const response = await api.post<{ message: string }>('/auth/logout');
    return response.data;
}

export async function verify() {
    const response = await api.get<VerifyResponse>('/auth/verify');
    return response.data;
}

export async function forgotPassword(forgottenPasswordPayload: ForgottenPasswordForm): Promise<{ message: string }> {
    const response = await api.post<{ message: string }>('/auth/forgot-password', forgottenPasswordPayload);
    return response.data;
}

export async function resetPassword(resetPasswordPayload: ResetPasswordPayload): Promise<{ message: string }> {
    const response = await api.post<{ message: string }>('/auth/reset-password', resetPasswordPayload);
    return response.data;
}
