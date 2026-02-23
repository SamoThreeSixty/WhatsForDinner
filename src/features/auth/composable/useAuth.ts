import {computed, getCurrentInstance, onBeforeUnmount, reactive, ref} from "vue";
import type {LoginForm} from "../types/login-form.js";
import {useAuthStore} from '@/stores/auth';
import * as authService from '@/features/auth/services/auth.service';
import {useRoute, useRouter} from "vue-router";
import type {RegisterForm} from "../types/register-form.js";
import type {ResetPasswordForm} from "../types/reset-password-form.js";
import type {ForgottenPasswordForm} from "../types/forgotten-password-form.js";
import axios from "axios";
import type {ApiErrorResponse} from "@/lib/api.ts";
import {useFeedbackStore} from '@/stores/feedback';

function getFirstValidationError(errors?: Record<string, string[]>) {
    return errors
        ? Object.values(errors).flat().find((message) => message.length > 0)
        : undefined;
}

function getApiErrorMessage(error: unknown, fallback: string) {
    if (!axios.isAxiosError<ApiErrorResponse>(error)) {
        return fallback;
    }

    const firstValidationError = getFirstValidationError(error.response?.data?.errors);
    return firstValidationError ?? error.response?.data?.message ?? fallback;
}

interface UseAuthOptions {
    feedbackScope?: string;
    feedbackInstanceId?: string;
}

export default function useAuth(options?: UseAuthOptions) {
    const loading = ref<boolean>(false);

    const loginForm = reactive<LoginForm>({
        email: '',
        password: ''
    });

    const registerForm = reactive<RegisterForm>({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    });

    const resetPasswordForm = reactive<ResetPasswordForm>({
        password: '',
        password_confirmation: ''
    });

    const forgottenPasswordForm = reactive<ForgottenPasswordForm>({
        email: ''
    });

    const authStore = useAuthStore();
    const feedbackStore = useFeedbackStore();
    const router = useRouter();
    const route = useRoute();
    const instanceUid = getCurrentInstance()?.uid ?? 0;

    const feedbackScope = computed(() => {
        const baseScope = options?.feedbackScope ?? `auth:${String(route.name ?? 'form')}`;
        const instanceId = options?.feedbackInstanceId ?? String(instanceUid);
        return `${baseScope}:${instanceId}`;
    });

    const feedback = computed(() => feedbackStore.forScope(feedbackScope.value));

    function clearFeedback() {
        feedbackStore.clear(feedbackScope.value);
    }

    function setSuccessFeedback(message: string) {
        feedbackStore.success(feedbackScope.value, message);
    }

    function setErrorFeedback(message: string) {
        feedbackStore.error(feedbackScope.value, message);
    }

    if (!feedback.value) {
        if (authStore.authError) {
            setErrorFeedback(authStore.authError);
        } else if (authStore.authMessage) {
            setSuccessFeedback(authStore.authMessage);
        }
    }

    async function login() {
        if (!loginForm.email || !loginForm.password || loading.value) {
            return;
        }

        loading.value = true;
        clearFeedback();

        try {
            await authStore.login({
                email: loginForm.email.trim(),
                password: loginForm.password
            });

            if (!authStore.isLoggedIn) {
                setErrorFeedback(authStore.authError || 'Unable to authenticate.');
                return;
            }

            if (!authStore.isVerified) {
                const message = 'Please verify your email before accessing the app.';
                authStore.authError = message;
                setErrorFeedback(message);
                return;
            }

            await router.push({name: 'app.dashboard'});
        } finally {
            loading.value = false;
        }
    }

    async function register() {
        if (
            loading.value ||
            !registerForm.name ||
            !registerForm.email ||
            !registerForm.password ||
            !registerForm.password_confirmation
        ) {
            return;
        }

        loading.value = true;
        clearFeedback();

        try {
            await authStore.register({
                name: registerForm.name.trim(),
                email: registerForm.email.trim(),
                password: registerForm.password,
                password_confirmation: registerForm.password_confirmation
            });

            if (!authStore.isLoggedIn) {
                setErrorFeedback(authStore.authError || 'Unable to create account.');
                return;
            }

            if (!authStore.isVerified) {
                const message = authStore.authMessage || 'Account created. Verify your email to continue.';
                authStore.authMessage = message;
                setSuccessFeedback(message);
                await router.push({name: 'auth.login'});
                return;
            }

            await router.push({name: 'app.dashboard'});
        } finally {
            loading.value = false;
        }
    }

    async function forgottenPassword() {
        if (
            loading.value ||
            !forgottenPasswordForm.email
        ) {
            return;
        }

        loading.value = true;
        clearFeedback();

        try {
            const result = await authService.forgotPassword({
                email: forgottenPasswordForm.email.trim()
            });
            const message = result.message || 'If an account exists, a reset link has been sent.';
            authStore.authError = '';
            authStore.authMessage = message;
            setSuccessFeedback(message);
        } catch (authError: unknown) {
            const message = getApiErrorMessage(authError, 'Unable to send password reset link.');
            authStore.authMessage = '';
            authStore.authError = message;
            setErrorFeedback(message);
        } finally {
            loading.value = false;
        }
    }

    async function resetPassword(token: string) {
        if (
            loading.value ||
            !token ||
            !resetPasswordForm.password ||
            !resetPasswordForm.password_confirmation
        ) {
            return;
        }

        if (resetPasswordForm.password !== resetPasswordForm.password_confirmation) {
            const message = 'Passwords do not match.';
            authStore.authMessage = '';
            authStore.authError = message;
            setErrorFeedback(message);
            return;
        }

        const email = String(router.currentRoute.value.query.email || '').trim();

        if (!email) {
            const message = 'Reset link is missing an email address. Please request a new one.';
            authStore.authMessage = '';
            authStore.authError = message;
            setErrorFeedback(message);
            return;
        }

        loading.value = true;
        clearFeedback();

        try {
            const result = await authService.resetPassword({
                token,
                email,
                password: resetPasswordForm.password,
                password_confirmation: resetPasswordForm.password_confirmation
            });

            const message = result.message || 'Password reset successfully. You can sign in now.';
            authStore.authError = '';
            authStore.authMessage = message;
            setSuccessFeedback(message);
            await router.push({name: 'auth.login'});
        } catch (authError: unknown) {
            const message = getApiErrorMessage(authError, 'Unable to reset password.');
            authStore.authMessage = '';
            authStore.authError = message;
            setErrorFeedback(message);
        } finally {
            loading.value = false;
        }
    }

    onBeforeUnmount(() => {
        clearFeedback();
    });

    return {
        loading,
        feedback,
        login,
        loginForm,
        register,
        registerForm,
        forgottenPassword,
        forgottenPasswordForm,
        resetPassword,
        resetPasswordForm,
    }
}
