<script setup lang="ts">
import Card from '@/features/auth/components/Card.vue';
import Title from '@/features/auth/components/Title.vue';
import AuthFeedback from '@/features/auth/components/AuthFeedback.vue';
import UiInput from '@/components/ui/Input.vue';
import UiButton from '@/components/ui/Button.vue';
import UiLabel from '@/components/ui/Label.vue';
import useAuth from '@/features/auth/composable/useAuth';

const {loading, feedback, resetPasswordForm, resetPassword} = useAuth();

const props = defineProps<{
    token: string
}>();
</script>

<template>
    <Card>
        <Title/>

        <p class="panel-title">Reset your password</p>
        <p class="panel-copy">Set a new password for your account.</p>

        <form class="login-form" @submit.prevent="resetPassword(props.token)">
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

        <AuthFeedback :feedback="feedback"/>

        <div class="route-links">
            <router-link class="auth-link-btn" to="/forgot-password">Request a new reset link</router-link>
            <router-link class="auth-link-btn" to="/login">Back to sign in</router-link>
        </div>
    </Card>
</template>
