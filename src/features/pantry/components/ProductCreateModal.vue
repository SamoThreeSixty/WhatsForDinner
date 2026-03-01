<script setup lang="ts">
import {watch} from "vue";
import DialogModal from "@/components/ui/DialogModal.vue";
import Input from "@/components/ui/Input.vue";
import Select from "@/components/ui/Select.vue";
import Label from "@/components/ui/Label.vue";
import Button from "@/components/ui/Button.vue";
import type {Product} from "@/features/pantry/types/product.ts";
import {useProductAdd} from "@/features/pantry/composable/useProductAdd.ts";

const props = defineProps<{
    modelValue: boolean;
    ingredientId: number | string;
}>();

const emit = defineEmits<{
    (event: "update:modelValue", value: boolean): void;
    (event: "created", value: Product): void;
}>();

const emitFn = (key: string, item: any) => emit(key, item);
const productAdd  = useProductAdd(emitFn);

watch(() => props.modelValue, async (isOpen) => {
    if (!isOpen) {
        return;
    }

    await productAdd.onOpen(props.ingredientId);
})

</script>

<template>
    <DialogModal
        :model-value="modelValue"
        title="Add Product"
        @update:model-value="(value) => emit('update:modelValue', value)"
    >
        <form class="grid gap-2" @submit.prevent="productAdd.submit">
            <div>
                <Label for="product-name">Company</Label>
                <Input id="product-name"
                       v-model="productAdd.form.value.company"
                />
            </div>

            <div>
                <Label for="product-name">Name</Label>
                <Input id="product-name"
                       v-model="productAdd.form.value.name"
                />
            </div>

            <div>
                <Label for="product-unit-type">Unit Type</Label>
                <Select
                    id="product-unit-type"
                    :model-value="productAdd.form.value.unitType"
                    :disabled="productAdd.metadataLoading.value"
                    @update:model-value="productAdd.onUnitTypeChange"
                >
                    <option value="" disabled>Select unit type</option>
                    <option v-for="type in productAdd.unitTypeOptions.value" :key="type.value" :value="type.value">
                        {{ type.label }}
                    </option>
                </Select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <Label for="product-unit-amount">Default Amount</Label>
                    <Input
                        id="product-unit-amount"
                        v-model="productAdd.form.value.unitAmount"
                        type="number"
                        min="0"
                        step="any"
                        placeholder="e.g. 250"
                    />
                </div>

                <div>
                    <Label for="product-unit-value">Default Unit</Label>
                    <Select
                        id="product-unit-value"
                        v-model="productAdd.form.value.unitValue"
                        :disabled="productAdd.unitValueOptions.value.length === 0"
                    >
                        <option value="" disabled>Select unit</option>
                        <option v-for="unit in productAdd.unitValueOptions.value" :key="unit" :value="unit">
                            {{ unit }}
                        </option>
                    </Select>
                </div>
            </div>

            <p v-if="productAdd.error.value" class="text-sm text-rose-700">{{ productAdd.error.value }}</p>
        </form>

        <template #footer>
            <button
                type="button"
                class="rounded-xl border border-emerald-900/20 px-3 py-2 text-sm font-semibold"
                @click="emit('update:modelValue', false)"
            >
                Cancel
            </button>
            <Button type="button" :disabled="productAdd.loading.value" @click="productAdd.submit">
                {{ productAdd.loading.value ? "Saving..." : "Save Product" }}
            </Button>
        </template>
    </DialogModal>
</template>
