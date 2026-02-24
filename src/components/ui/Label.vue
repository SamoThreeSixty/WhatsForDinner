<script setup lang="ts">
import {useAttrs} from 'vue';

defineOptions({inheritAttrs: false});

const attrs = useAttrs();
</script>

<template>
    <label class="ui-label" v-bind="attrs">
        <slot/>
    </label>
</template>

<style scoped>
.ui-label {
    color: var(--green-ink);
    font-size: 0.9rem;
    font-weight: 600;
}
</style>
