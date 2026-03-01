<script setup lang="ts">
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';

export interface MultiSelectOption {
    label: string;
    value: string;
}

const props = withDefaults(defineProps<{
    modelValue: string[];
    options: MultiSelectOption[];
    loading?: boolean;
    placeholder?: string;
    noResultsText?: string;
    disabled?: boolean;
}>(), {
    placeholder: 'Search options...',
    noResultsText: 'No options found',
    loading: false,
    disabled: false,
});

const emit = defineEmits<{
    (event: 'update:modelValue', value: string[]): void;
    (event: 'search', value: string): void;
}>();

const rootRef = ref<HTMLElement | null>(null);
const isOpen = ref(false);
const query = ref('');

const selectedValues = computed<string[]>(() => {
    return Array.isArray(props.modelValue) ? props.modelValue : [];
});

const selectedSet = computed<Set<string>>(() => new Set(selectedValues.value));

const selectedOptions = computed<MultiSelectOption[]>(() => {
    return props.options.filter((option) => selectedSet.value.has(option.value));
});

const filteredOptions = computed<MultiSelectOption[]>(() => {
    const q = query.value.trim().toLowerCase();
    const base = props.options.filter((option) => !selectedSet.value.has(option.value));

    if (q === '') {
        return base;
    }

    return base.filter((option) => option.label.toLowerCase().includes(q));
});

function openMenu() {
    if (props.disabled) {
        return;
    }

    isOpen.value = true;
}

function closeMenu() {
    isOpen.value = false;
}

function onSearch(value: string) {
    query.value = value;
    emit('search', value);
}

function addValue(option: MultiSelectOption) {
    const next = [...selectedValues.value, option.value];
    emit('update:modelValue', next);
    query.value = '';
    emit('search', '');
}

function removeValue(value: string) {
    const next = selectedValues.value.filter((item) => item !== value);
    emit('update:modelValue', next);
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

watch(() => props.disabled, (disabled) => {
    if (disabled) {
        closeMenu();
    }
});

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside);
});
</script>

<template>
    <div ref="rootRef" class="ui-multi-select">
        <div
            class="ui-multi-select__control"
            :class="{'ui-multi-select__control--open': isOpen}"
            @click="openMenu"
        >
            <div v-if="selectedOptions.length > 0" class="ui-multi-select__chips">
                <button
                    v-for="option in selectedOptions"
                    :key="option.value"
                    type="button"
                    class="ui-multi-select__chip"
                    :disabled="props.disabled"
                    @click.stop="removeValue(option.value)"
                >
                    {{ option.label }}
                    <span aria-hidden="true">x</span>
                </button>
            </div>

            <input
                :value="query"
                type="text"
                class="ui-multi-select__input"
                :placeholder="selectedOptions.length === 0 ? props.placeholder : 'Add more...'"
                :disabled="props.disabled"
                @focus="openMenu"
                @input="onSearch(($event.target as HTMLInputElement).value)"
            />

            <span class="ui-multi-select__caret" aria-hidden="true">▾</span>
        </div>

        <div v-if="isOpen" class="ui-multi-select__menu">
            <p v-if="props.loading" class="ui-multi-select__state">Searching...</p>
            <p v-else-if="filteredOptions.length === 0" class="ui-multi-select__state">{{ props.noResultsText }}</p>
            <button
                v-for="option in filteredOptions"
                v-else
                :key="option.value"
                type="button"
                class="ui-multi-select__option"
                @click="addValue(option)"
            >
                {{ option.label }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.ui-multi-select {
    position: relative;
}

.ui-multi-select__control {
    display: flex;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 0.4rem;
    border: 1px solid rgba(23, 86, 57, 0.2);
    border-radius: 12px;
    background: #fff;
    min-height: 46px;
    padding: 0.45rem 0.6rem;
}

.ui-multi-select__control--open {
    outline: 2px solid var(--lime-pop);
    outline-offset: 1px;
}

.ui-multi-select__chips {
    display: flex;
    flex-wrap: wrap;
    flex: 1 1 auto;
    gap: 0.35rem;
}

.ui-multi-select__chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1px solid rgba(23, 86, 57, 0.18);
    border-radius: 999px;
    background: rgba(184, 239, 79, 0.2);
    color: var(--green-ink);
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    cursor: pointer;
}

.ui-multi-select__input {
    flex: 1 1 120px;
    width: auto;
    min-width: 80px;
    border: 0;
    background: transparent;
    color: var(--green-ink);
    font-size: 0.95rem;
}

.ui-multi-select__input:focus {
    outline: none;
}

.ui-multi-select__caret {
    margin-left: auto;
    align-self: center;
    color: var(--green-muted);
    font-size: 0.85rem;
}

.ui-multi-select__menu {
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

.ui-multi-select__state {
    font-size: 0.85rem;
    color: var(--green-muted);
    padding: 0.5rem 0.6rem;
}

.ui-multi-select__option {
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

.ui-multi-select__option:hover {
    background: rgba(184, 239, 79, 0.35);
}
</style>
