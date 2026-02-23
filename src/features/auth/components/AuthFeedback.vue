<script setup lang="ts">
import type {FeedbackState} from '@/stores/feedback';

const props = defineProps<{
    feedback: FeedbackState | null;
}>();
</script>

<template>
    <p
        v-if="props.feedback"
        class="auth-feedback"
        :class="props.feedback.type === 'error' ? 'auth-feedback-error' : 'auth-feedback-success'"
        role="status"
        aria-live="polite"
    >
        {{ props.feedback.message }}
    </p>
</template>

<style scoped>
.auth-feedback {
    margin-top: 0.2rem;
    border-radius: 12px;
    border: 1px solid transparent;
    padding: 0.7rem 0.8rem;
    font-size: 0.9rem;
    line-height: 1.45;
    font-weight: 600;
}

.auth-feedback-error {
    border-color: rgba(190, 24, 93, 0.18);
    background: rgba(255, 241, 242, 0.95);
    color: #9f1239;
}

.auth-feedback-success {
    border-color: rgba(4, 120, 87, 0.2);
    background: rgba(236, 253, 245, 0.95);
    color: #065f46;
}
</style>
