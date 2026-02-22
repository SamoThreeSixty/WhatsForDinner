<script setup lang="ts">
import Card from '@src/features/auth/components/Card.vue';
import Title from '@src/features/auth/components/Title.vue';
import UiInput from '@src/components/ui/Input.vue';
import UiButton from '@src/components/ui/Button.vue';
import UiLabel from '@src/components/ui/Label.vue';
import useAuth from "@src/features/auth/composable/useAuth.js";

const {error, loading, resetPasswordForm, resetPassword} = useAuth();

const props = defineProps<{
    token: string
}>();
</script>

<template>
    <Card>
        <Title/>

        <p class="panel-title">Reset your password</p>
        <p class="panel-copy">Set a new password for your account.</p>

        <form class="login-form" @submit.prevent="resetPassword">
            <UiLabel for="reset-password">New Password</UiLabel>
            <UiInput id="reset-password"
                     v-model="resetPasswordForm.password"
                     type="password"
                     placeholder="At least 8 characters"
                     required
            />

            <UiLabel for="reset-password-confirmation">Confirm New Password</UiLabel>
            <UiInput id="reset-password-confirmation"
                     v-model="resetPasswordForm.password_confirmation"
                     type="password"
                     placeholder="Repeat password"
                     required
            />

            <UiButton class="auth-primary-btn" type="submit" :disabled="loading">
                Update Password
            </UiButton>
        </form>

        <p v-if="error" class="error-msg">{{ error }}</p>

        <div class="route-links">
            <router-link class="auth-link-btn" to="/forgot-password">Request a new reset link</router-link>
            <router-link class="auth-link-btn" to="/login">Back to sign in</router-link>
        </div>
    </Card>
</template>
