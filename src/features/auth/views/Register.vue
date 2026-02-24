<script setup lang="ts">
import Card from '@/features/auth/components/Card.vue';
import Title from '@/features/auth/components/Title.vue';
import AuthFeedback from '@/features/auth/components/AuthFeedback.vue';
import UiInput from '@/components/ui/Input.vue';
import UiButton from '@/components/ui/Button.vue';
import UiLabel from '@/components/ui/Label.vue';
import useAuth from '@/features/auth/composable/useAuth';

const {loading, feedback, registerForm, register} = useAuth();
</script>

<template>
    <Card>
        <Title/>

        <form class="login-form" @submit.prevent="register">
            <UiLabel for="register-name">Name</UiLabel>
            <UiInput id="register-name"
                     v-model="registerForm.name"
                     placeholder="Alex"
                     type="text"
                     required
            />

            <UiLabel for="register-email">Email</UiLabel>
            <UiInput id="register-email"
                     v-model="registerForm.email"
                     placeholder="you@example.com"
                     type="email"
                     required
            />

            <UiLabel for="register-password">Password</UiLabel>
            <UiInput id="register-password"
                     v-model="registerForm.password"
                     placeholder="At least 8 characters"
                     type="password"
                     required
            />

            <UiLabel for="register-password-confirm">Confirm Password</UiLabel>
            <UiInput id="register-password-confirm"
                     v-model="registerForm.password_confirmation"
                     placeholder="Repeat password"
                     type="password"
                     required
            />

            <UiButton class="auth-primary-btn" type="submit" :disabled="loading">
                {{ loading ? 'Creating account...' : 'Create Account' }}
            </UiButton>
        </form>

        <AuthFeedback :feedback="feedback"/>

        <div class="route-links">
            <router-link class="auth-link-btn" to="/login">Already have an account? Sign in</router-link>
            <router-link class="auth-link-btn" to="/forgot-password">Forgot password?</router-link>
        </div>
    </Card>
</template>
