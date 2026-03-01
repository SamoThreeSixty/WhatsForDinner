<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';

export interface SearchOption {
    label: string;
    value: string | number;
}

const props = withDefaults(defineProps<{
    modelValue?: string | number | null;
    options: SearchOption[];
    loading?: boolean;
    placeholder?: string;
    noResultsText?: string;
    disabled?: boolean;
    clearable?: boolean;
}>(), {
    placeholder: 'Search...',
    noResultsText: 'No results found',
    loading: false,
    disabled: false,
    clearable: true,
});

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
    (event: 'search', value: string): void;
    (event: 'change', value: string): void;
}>();

const rootRef = ref<HTMLElement | null>(null);
const inputRef = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);
const query = ref('');
const highlightedIndex = ref(-1);

const selectedOption = computed(() => {
    if (props.modelValue === null || props.modelValue === undefined || props.modelValue === '') {
        return null;
    }

    const value = String(props.modelValue);
    return props.options.find((option) => String(option.value) === value) ?? null;
});

const showClearButton = computed(() => {
    if (!props.clearable || props.disabled) {
        return false;
    }

    return query.value.trim().length > 0 || selectedOption.value !== null;
});

watch(() => props.modelValue, (value) => {
    if (document.activeElement === inputRef.value) {
        return;
    }

    query.value = selectedOption.value?.label ?? String(value ?? '');
});

function openMenu() {
    if (props.disabled) {
        return;
    }

    isOpen.value = true;
    highlightedIndex.value = props.options.length > 0 ? 0 : -1;
}

function closeMenu() {
    isOpen.value = false;
    highlightedIndex.value = -1;
}

function onInput(event: Event) {
    const target = event.target as HTMLInputElement;
    query.value = target.value;
    emit('update:modelValue', query.value);
    emit('search', query.value);

    if (!isOpen.value) {
        openMenu();
    }
}

function selectOption(option: SearchOption) {
    query.value = option.label;
    const value = String(option.value);
    emit('update:modelValue', value);
    emit('change', value);
    closeMenu();
}

function onKeydown(event: KeyboardEvent) {
    if (props.disabled) {
        return;
    }

    if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (!isOpen.value) {
            openMenu();
            return;
        }
        highlightedIndex.value = Math.min(highlightedIndex.value + 1, props.options.length - 1);
        return;
    }

    if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (!isOpen.value) {
            openMenu();
            return;
        }
        highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
        return;
    }

    if (event.key === 'Enter') {
        if (!isOpen.value) {
            openMenu();
            return;
        }

        if (highlightedIndex.value >= 0 && props.options[highlightedIndex.value]) {
            event.preventDefault();
            const selected = props.options[highlightedIndex.value];

            if (selected) {
                selectOption(selected);
            }
        }
        return;
    }

    if (event.key === 'Escape') {
        closeMenu();
    }
}

function onFocus() {
    openMenu();
}

function clearSelection() {
    query.value = '';
    emit('update:modelValue', '');
    emit('search', '');
    openMenu();
    inputRef.value?.focus();
}

function onClickOutside(event: MouseEvent) {
    if (!rootRef.value) {
        return;
    }

    const target = event.target as Node | null;

    if (target && !rootRef.value.contains(target)) {
        closeMenu();
    }
}

onMounted(() => {
    query.value = typeof props.modelValue === 'string' ? props.modelValue : selectedOption.value?.label ?? '';
    document.addEventListener('mousedown', onClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside);
});
</script>

<template>
    <div ref="rootRef" class="search-selection">
        <div class="search-selection__control" :class="{'search-selection__control--open': isOpen}">
            <input
                ref="inputRef"
                type="text"
                class="search-selection__input"
                :placeholder="placeholder"
                :value="query"
                :disabled="disabled"
                @focus="onFocus"
                @keydown="onKeydown"
                @input="onInput"
            />
            <button
                v-if="showClearButton"
                type="button"
                class="search-selection__clear"
                :disabled="disabled"
                aria-label="Clear"
                @click="clearSelection"
            >
                x
            </button>
            <span class="search-selection__caret" aria-hidden="true">▾</span>
        </div>

        <div v-if="isOpen" class="search-selection__menu">
            <p v-if="loading" class="search-selection__state">Searching...</p>
            <p v-else-if="options.length === 0" class="search-selection__state">{{ noResultsText }}</p>
            <button
                v-for="(option, index) in options"
                v-else
                :key="String(option.value)"
                type="button"
                class="search-selection__option"
                :class="{'search-selection__option--active': highlightedIndex === index}"
                @mouseenter="highlightedIndex = index"
                @click="selectOption(option)"
            >
                {{ option.label }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.search-selection {
    position: relative;
}

.search-selection__control {
    display: flex;
    align-items: center;
    border: 1px solid rgba(23, 86, 57, 0.2);
    border-radius: 12px;
    background: #fff;
    min-height: 46px;
    padding-right: 0.5rem;
}

.search-selection__control--open {
    outline: 2px solid var(--lime-pop);
    outline-offset: 1px;
}

.search-selection__input {
    width: 100%;
    min-width: 0;
    border: 0;
    background: transparent;
    color: var(--green-ink);
    padding: 0.8rem 0.9rem;
    font-size: 1rem;
}

.search-selection__input:focus {
    outline: none;
}

.search-selection__clear {
    border: 0;
    background: transparent;
    color: var(--green-muted);
    font-size: 0.8rem;
    line-height: 1;
    cursor: pointer;
    margin-right: 0.35rem;
}

.search-selection__caret {
    color: var(--green-muted);
    font-size: 0.85rem;
}

.search-selection__menu {
    position: absolute;
    z-index: 40;
    top: calc(100% + 0.35rem);
    left: 0;
    right: 0;
    border: 1px solid rgba(23, 86, 57, 0.2);
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 12px 20px rgba(17, 60, 45, 0.12);
    padding: 0.3rem;
    max-height: 220px;
    overflow: auto;
}

.search-selection__state {
    font-size: 0.85rem;
    color: var(--green-muted);
    padding: 0.5rem 0.6rem;
}

.search-selection__option {
    width: 100%;
    border: 0;
    background: transparent;
    text-align: left;
    color: var(--green-ink);
    border-radius: 8px;
    padding: 0.48rem 0.6rem;
    cursor: pointer;
    font-size: 0.92rem;
}

.search-selection__option--active {
    background: rgba(184, 239, 79, 0.35);
}
</style>
