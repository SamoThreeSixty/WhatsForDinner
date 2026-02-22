<script setup lang="ts">
import Card from '@src/features/auth/components/Card.vue';
import Title from '@src/features/auth/components/Title.vue';
import UiInput from '@src/components/ui/Input.vue';
import UiButton from '@src/components/ui/Button.vue';
import UiLabel from '@src/components/ui/Label.vue';
import useAuth from "@src/features/auth/composable/useAuth.js";

const {error, loading, registerForm, register} = useAuth();

</script>

<template>
    <Card>
        <Title/>

        <form class="login-form" @submit.prevent="register">
            <UiLabel for="register-name">Name</UiLabel>
            <UiInput id="register-name" v-model="registerForm.name" placeholder="Alex" type="text" required/>

            <UiLabel for="register-email">Email</UiLabel>
            <UiInput id="register-email" v-model="registerForm.email" placeholder="you@example.com" type="email"
                     required/>

            <UiLabel for="register-password">Password</UiLabel>
            <UiInput id="register-password" v-model="registerForm.password" placeholder="At least 8 characters"
                     type="password" required/>

            <UiLabel for="register-password-confirm">Confirm Password</UiLabel>
            <UiInput id="register-password-confirm" v-model="registerForm.password_confirmation"
                     placeholder="Repeat password" type="password" required/>

            <UiButton class="auth-primary-btn" type="submit" :disabled="loading">
                {{ loading ? 'Creating account...' : 'Create Account' }}
            </UiButton>
        </form>

        <div class="route-links">
            <router-link class="auth-link-btn" to="/login">Already have an account? Sign in</router-link>
            <router-link class="auth-link-btn" to="/forgot-password">Forgot password?</router-link>
        </div>
    </Card>
</template>
