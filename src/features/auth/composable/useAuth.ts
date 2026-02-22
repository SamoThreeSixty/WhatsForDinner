import {reactive, ref} from "vue";
import type {LoginForm} from "../types/login-form.js";
import {useAuthStore} from '@src/stores/auth';
import {useRouter} from "vue-router";
import type {RegisterForm} from "../types/register-form.js";
import type {ResetPasswordForm} from "../types/reset-password-form.js";
import type {ForgottenPasswordForm} from "../types/forgotten-password-form.js";

export default function useAuth() {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const loginForm = reactive<LoginForm>({
        email: '',
        password: ''
    });

    const registerForm = reactive<RegisterForm>({
        name: '',
        email: '',
        password: '',
        confirm_password: ''
    });

    const resetPasswordForm = reactive<ResetPasswordForm>({
        password: '',
        password_confirmation: ''
    });

    const forgottenPasswordForm = reactive<ForgottenPasswordForm>({
        email: ''
    })

    const authStore = useAuthStore();
    const router = useRouter();

    async function login() {
        if (!loginForm.email || !loginForm.password || loading.value) {
            return;
        }

        loading.value = true;

        try {
            await authStore.login({
                email: loginForm.email.trim(),
                password: loginForm.password
            });

            if (!authStore.isVerified) {
                authStore.authError = 'Please verify your email before accessing the app.';
                return;
            }

            router.push('/home');
        } finally {
            loading.value = false;
        }
    }

    async function register() {
        console.log("TODO: register")
    }

    async function forgottenPassword() {
        console.log("TODO: forgotten password")
    }

    async function resetPassword() {
        console.log("TODO: reset password")
    }

    return {
        loading,
        error,
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
