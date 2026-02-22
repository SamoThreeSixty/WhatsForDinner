<script setup lang="ts">
import Card from '@src/features/auth/components/Card.vue';
import Title from '@src/features/auth/components/Title.vue';
import UiInput from '@src/components/ui/Input.vue';
import UiButton from '@src/components/ui/Button.vue';
import UiLabel from '@src/components/ui/Label.vue';
import useAuth from '@src/features/auth/composable/useAuth';

const {loading, login, loginForm} = useAuth();

</script>

<template>
    <Card>
        <Title/>

        <form class="grid gap-2" @submit.prevent="login">
            <UiLabel for="login-email">Email</UiLabel>
            <UiInput class="!mb-0" id="login-email" v-model="loginForm.email" placeholder="you@example.com" type="email" required/>

            <UiLabel for="login-password">Password</UiLabel>
            <UiInput class="!mb-0" id="login-password" v-model="loginForm.password" placeholder="Password" type="password" required/>

            <UiButton class="auth-primary-btn mt-2" type="submit" :disabled="loading">
                {{ loading ? 'Logging in...' : 'Login To Dashboard' }}
            </UiButton>
        </form>

        <div class="mt-4 flex flex-wrap gap-4">
            <router-link class="auth-link-btn" to="/register">Create account</router-link>
            <router-link class="auth-link-btn" to="/forgot-password">Forgot password?</router-link>
        </div>
    </Card>
</template>
