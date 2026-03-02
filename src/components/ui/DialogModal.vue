<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref, watch} from 'vue';

const props = withDefaults(defineProps<{
    modelValue: boolean;
    title?: string;
    closeOnBackdrop?: boolean;
}>(), {
    title: '',
    closeOnBackdrop: true,
});

const emit = defineEmits<{
    (event: 'update:modelValue', value: boolean): void;
    (event: 'close'): void;
}>();

const dialogRef = ref<HTMLDialogElement | null>(null);

function closeDialog() {
    emit('update:modelValue', false);
    emit('close');
}

function syncOpenState(isOpen: boolean) {
    if (!dialogRef.value) {
        return;
    }

    if (isOpen && !dialogRef.value.open) {
        dialogRef.value.showModal();
        return;
    }

    if (!isOpen && dialogRef.value.open) {
        dialogRef.value.close();
    }
}

function onDialogClose() {
    if (props.modelValue) {
        closeDialog();
    }
}

function onDialogCancel() {
    closeDialog();
}

function onBackdropClick(event: MouseEvent) {
    if (!props.closeOnBackdrop) {
        return;
    }

    if (event.target === dialogRef.value) {
        closeDialog();
    }
}

watch(() => props.modelValue, syncOpenState, {immediate: true});

onMounted(() => {
    if (!dialogRef.value) {
        return;
    }

    dialogRef.value.addEventListener('close', onDialogClose);
    dialogRef.value.addEventListener('cancel', onDialogCancel);
});

onBeforeUnmount(() => {
    if (!dialogRef.value) {
        return;
    }

    dialogRef.value.removeEventListener('close', onDialogClose);
    dialogRef.value.removeEventListener('cancel', onDialogCancel);
});
</script>

<template>
    <dialog ref="dialogRef" class="ui-dialog" @click="onBackdropClick">
        <article class="ui-dialog__panel" @click.stop>
            <header class="ui-dialog__header">
                <h2 class="ui-dialog__title">{{ title }}</h2>
                <button type="button" class="ui-dialog__close" aria-label="Close dialog" @click="closeDialog">x</button>
            </header>

            <div class="ui-dialog__body">
                <slot />
            </div>

            <footer v-if="$slots.footer" class="ui-dialog__footer">
                <slot name="footer" />
            </footer>
        </article>
    </dialog>
</template>

<style scoped>
.ui-dialog {
    border: 0;
    padding: 0;
    border-radius: 16px;
    background: transparent;
    width: min(96vw, 560px);
    margin: auto;
    max-height: calc(100vh - 2rem);
}

.ui-dialog::backdrop {
    background: rgba(6, 34, 20, 0.44);
}

.ui-dialog__panel {
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1px solid rgba(23, 86, 57, 0.16);
    border-radius: 16px;
    box-shadow: 0 30px 80px rgba(8, 40, 25, 0.3);
    overflow: hidden;
    max-height: inherit;
}

.ui-dialog__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1rem 0.8rem;
    border-bottom: 1px solid rgba(23, 86, 57, 0.12);
}

.ui-dialog__title {
    margin: 0;
    color: var(--green-ink);
    font-size: 1.05rem;
    font-weight: 700;
}

.ui-dialog__close {
    border: 0;
    background: transparent;
    color: var(--green-ink);
    cursor: pointer;
    border-radius: 8px;
    padding: 0.25rem 0.45rem;
    font-weight: 700;
}

.ui-dialog__body {
    padding: 1rem;
    overflow-y: auto;
    min-height: 0;
}

.ui-dialog__footer {
    display: flex;
    gap: 0.55rem;
    justify-content: flex-end;
    padding: 0 1rem 1rem;
}
</style>
