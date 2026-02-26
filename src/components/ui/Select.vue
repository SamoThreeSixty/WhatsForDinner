<script setup lang="ts">
import {useAttrs} from 'vue';

defineOptions({inheritAttrs: false});

const props = defineProps<{
    modelValue?: string | number | null;
    disabled?: boolean;
}>();

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
}>();

const attrs = useAttrs();

function onChange(event: Event) {
    const target = event.target as HTMLSelectElement;
    emit('update:modelValue', target.value);
}
</script>

<template>
    <select
        class="ui-select"
        v-bind="attrs"
        :value="props.modelValue ?? ''"
        :disabled="props.disabled"
        @change="onChange"
    >
        <slot />
    </select>
</template>

<style scoped>
.ui-select {
    display: block;
    width: 100%;
    min-width: 0;
    border: 1px solid rgba(23, 86, 57, 0.2);
    border-radius: 12px;
    padding: 0.8rem 0.9rem;
    font-size: 1rem;
    background: #fff;
    color: var(--green-ink);
    margin-bottom: 0.35rem;
}

.ui-select:focus {
    outline: 2px solid var(--lime-pop);
    outline-offset: 1px;
}

.ui-select:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
