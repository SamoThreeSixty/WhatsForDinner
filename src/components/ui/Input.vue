<script setup lang="ts">
import {useAttrs} from 'vue';

defineOptions({inheritAttrs: false});

const props = defineProps<{
    modelValue?: string | number | null;
}>();

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
}>();

const attrs = useAttrs();

const onInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>

<template>
    <input
        class="ui-input"
        v-bind="attrs"
        :value="props.modelValue ?? ''"
        @input="onInput"
    />
</template>

<style scoped>
.ui-input {
    display: block;
    border: 1px solid rgba(23, 86, 57, 0.2);
    border-radius: 12px;
    padding: 0.8rem 0.9rem;
    font-size: 1rem;
    background: #fff;
    color: var(--green-ink);
    margin-bottom: 0.35rem;
}

.ui-input:focus {
    outline: 2px solid var(--lime-pop);
    outline-offset: 1px;
}
</style>
