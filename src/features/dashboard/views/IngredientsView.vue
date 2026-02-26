<script setup lang="ts">
import {computed, onMounted, reactive, ref} from 'vue';
import axios from 'axios';
import PageHeader from '@/components/PageHeader.vue';
import type {ApiErrorResponse} from '@/lib/api';
import {
    createIngredient,
    deleteIngredient,
    listIngredients,
    updateIngredient,
} from '@/features/dashboard/services/ingredient.service';
import type {Ingredient, IngredientPayload, UnitType} from '@/features/dashboard/types/ingredient';

const loading = ref(false);
const saving = ref(false);
const message = ref('');
const error = ref('');
const editingId = ref<number | null>(null);
const ingredients = ref<Ingredient[]>([]);

const unitTypeOptions: UnitType[] = ['count', 'mass', 'volume'];
const unitCodeMap: Record<UnitType, string[]> = {
    count: ['each', 'pack', 'bag'],
    mass: ['g', 'kg'],
    volume: ['ml', 'l']
};

const form = reactive({
    name: '',
    quantity: '1',
    unit_type: 'count' as UnitType,
    unit: 'each',
    category: '',
    location: '',
    purchased_at: '',
    expires_at: '',
    batch_reference: ''
});

const availableUnits = computed(() => unitCodeMap[form.unit_type]);

function getApiErrorMessage(err: unknown, fallback: string) {
    if (!axios.isAxiosError<ApiErrorResponse>(err)) {
        return fallback;
    }

    const first = err.response?.data?.errors
        ? Object.values(err.response.data.errors).flat().find(Boolean)
        : undefined;

    return first ?? err.response?.data?.message ?? fallback;
}

function resetFeedback() {
    message.value = '';
    error.value = '';
}

function resetForm() {
    form.name = '';
    form.quantity = '1';
    form.unit_type = 'count';
    form.unit = 'each';
    form.category = '';
    form.location = '';
    form.purchased_at = '';
    form.expires_at = '';
    form.batch_reference = '';
    editingId.value = null;
}

function toPayload(): IngredientPayload {
    const quantity = Number(form.quantity);

    return {
        name: form.name.trim(),
        quantity: Number.isFinite(quantity) ? quantity : 0,
        unit_type: form.unit_type,
        unit: form.unit,
        category: form.category.trim() || null,
        location: form.location.trim() || null,
        purchased_at: form.purchased_at || null,
        expires_at: form.expires_at || null,
        batch_reference: form.batch_reference.trim() || null,
    };
}

function startEdit(item: Ingredient) {
    editingId.value = item.id;
    form.name = item.name;
    form.quantity = String(item.quantity);
    form.unit_type = item.unit_type;
    form.unit = item.unit;
    form.category = item.category ?? '';
    form.location = item.location ?? '';
    form.purchased_at = item.purchased_at ? item.purchased_at.slice(0, 10) : '';
    form.expires_at = item.expires_at ? item.expires_at.slice(0, 10) : '';
    form.batch_reference = item.batch_reference ?? '';
    resetFeedback();
}

async function fetchIngredients() {
    loading.value = true;
    resetFeedback();

    try {
        ingredients.value = await listIngredients();
    } catch (err: unknown) {
        error.value = getApiErrorMessage(err, 'Unable to load ingredients.');
    } finally {
        loading.value = false;
    }
}

async function submitForm() {
    if (saving.value) {
        return;
    }

    saving.value = true;
    resetFeedback();

    try {
        if (editingId.value) {
            const updated = await updateIngredient(editingId.value, toPayload());
            ingredients.value = ingredients.value.map((item) => (item.id === updated.id ? updated : item));
            message.value = 'Ingredient updated.';
        } else {
            const created = await createIngredient(toPayload());
            ingredients.value.unshift(created);
            message.value = 'Ingredient added.';
        }

        resetForm();
    } catch (err: unknown) {
        error.value = getApiErrorMessage(err, 'Unable to save ingredient.');
    } finally {
        saving.value = false;
    }
}

async function removeIngredient(id: number) {
    if (!window.confirm('Delete this ingredient?')) {
        return;
    }

    resetFeedback();

    try {
        await deleteIngredient(id);
        ingredients.value = ingredients.value.filter((item) => item.id !== id);
        if (editingId.value === id) {
            resetForm();
        }
        message.value = 'Ingredient removed.';
    } catch (err: unknown) {
        error.value = getApiErrorMessage(err, 'Unable to delete ingredient.');
    }
}

onMounted(fetchIngredients);
</script>

<template>
    <section class="space-y-4">
        <PageHeader
            eyebrow="Larder"
            title="Ingredients"
            subtitle="See everything you have and manage your stock with quick add, edit, and remove actions."
        />

        <section class="rounded-2xl border border-emerald-800/10 bg-white/78 p-4 shadow-[0_10px_22px_rgba(8,72,43,0.1)] md:p-5">
            <h2 class="text-xs font-semibold uppercase tracking-[0.11em] text-emerald-800/85">
                {{ editingId ? 'Edit ingredient' : 'Add ingredient' }}
            </h2>

            <form class="mt-3 grid gap-2 md:grid-cols-2" @submit.prevent="submitForm">
                <input v-model="form.name" type="text" required placeholder="Name" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm" />
                <input v-model="form.quantity" type="number" step="0.001" min="0" required placeholder="Quantity" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm" />

                <select v-model="form.unit_type" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm">
                    <option v-for="unitType in unitTypeOptions" :key="unitType" :value="unitType">{{ unitType }}</option>
                </select>

                <select v-model="form.unit" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm">
                    <option v-for="unit in availableUnits" :key="unit" :value="unit">{{ unit }}</option>
                </select>

                <input v-model="form.category" type="text" placeholder="Category (optional)" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm" />
                <input v-model="form.location" type="text" placeholder="Location (optional)" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm" />

                <input v-model="form.purchased_at" type="date" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm" />
                <input v-model="form.expires_at" type="date" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm" />

                <input v-model="form.batch_reference" type="text" placeholder="Batch reference (optional)" class="rounded-xl border border-emerald-900/15 px-3 py-2.5 text-sm md:col-span-2" />

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white" :disabled="saving">
                        {{ saving ? 'Saving...' : editingId ? 'Update ingredient' : 'Add ingredient' }}
                    </button>
                    <button v-if="editingId" type="button" class="rounded-xl border border-emerald-900/20 px-4 py-2.5 text-sm font-semibold" @click="resetForm">
                        Cancel edit
                    </button>
                </div>
            </form>

            <p v-if="message" class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">{{ message }}</p>
            <p v-if="error" class="mt-3 rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700">{{ error }}</p>
        </section>

        <section class="rounded-2xl border border-emerald-800/10 bg-white/78 p-4 shadow-[0_10px_22px_rgba(8,72,43,0.1)] md:p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-[0.11em] text-emerald-800/85">All ingredients</h2>
                <button type="button" class="rounded-lg border border-emerald-900/20 px-3 py-1.5 text-xs font-semibold" :disabled="loading" @click="fetchIngredients">
                    {{ loading ? 'Refreshing...' : 'Refresh' }}
                </button>
            </div>

            <p v-if="!loading && ingredients.length === 0" class="mt-3 text-sm text-[var(--green-muted)]">No ingredients yet.</p>

            <ul v-else class="mt-3 space-y-2">
                <li v-for="item in ingredients" :key="item.id" class="flex items-start justify-between gap-3 rounded-xl border border-emerald-900/10 bg-emerald-50/40 px-3 py-2.5">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-[var(--green-ink)]">{{ item.name }}</p>
                        <p class="text-xs text-[var(--green-muted)]">
                            {{ item.quantity }} {{ item.unit }} · {{ item.unit_type }}
                            <span v-if="item.location"> · {{ item.location }}</span>
                            <span v-if="item.category"> · {{ item.category }}</span>
                        </p>
                    </div>
                    <div class="shrink-0 flex gap-2">
                        <button type="button" class="text-xs font-semibold text-emerald-800 hover:underline" @click="startEdit(item)">Edit</button>
                        <button type="button" class="text-xs font-semibold text-rose-700 hover:underline" @click="removeIngredient(item.id)">Delete</button>
                    </div>
                </li>
            </ul>
        </section>
    </section>
</template>
