<script setup lang="ts">
import Card from '@src/features/auth/components/Card.vue';
import Title from '@src/features/auth/components/Title.vue';
import UiInput from '@src/components/ui/Input.vue';
import UiButton from '@src/components/ui/Button.vue';
import UiLabel from '@src/components/ui/Label.vue';
import useAuth from "@src/features/auth/composable/useAuth.js";

const {error, loading, forgottenPasswordForm, forgottenPassword} = useAuth()

</script>

<template>
    <Card>
        <Title/>

        <p class="panel-title">Forgot your password?</p>
        <p class="panel-copy">Enter your account email and we will send a reset link.</p>

        <form class="login-form" @submit.prevent="forgottenPassword">
            <UiLabel for="forgot-email">Email</UiLabel>
            <UiInput id="forgot-email" v-model="forgottenPasswordForm.email" type="email" placeholder="you@example.com"
                     required/>

            <UiButton class="auth-primary-btn" type="submit" :disabled="loading">
                Send password reset
            </UiButton>
        </form>

        <div class="route-links">
            <router-link class="auth-link-btn" to="/login">Go back to login</router-link>
        </div>
    </Card>
</template>
