<script setup lang="ts">
import {useAttrs} from 'vue';

defineOptions({inheritAttrs: false});

const props = withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
    }>(),
    {
        type: 'button',
        disabled: false
    }
);

const attrs = useAttrs();
</script>

<template>
    <button class="ui-button" v-bind="attrs" :type="props.type" :disabled="props.disabled">
        <slot/>
    </button>
</template>

<style scoped>
.ui-button {
    border: 0;
    border-radius: 999px;
    background: linear-gradient(120deg, var(--green-solid), var(--green-deep));
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    padding: 0.82rem 1.2rem;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.ui-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 28px rgba(9, 102, 58, 0.35);
}

.ui-button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}
</style>
