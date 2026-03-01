<script setup lang="ts">
import {computed, onMounted, ref, watch} from "vue";
import DialogModal from "@/components/ui/DialogModal.vue";
import Input from "@/components/ui/Input.vue";
import Select from "@/components/ui/Select.vue";
import Label from "@/components/ui/Label.vue";
import Button from "@/components/ui/Button.vue";
import type {CreateProductPayload, Product} from "@/features/pantry/types/product.ts";
import type {UnitType} from "@/features/pantry/types/unit.ts";
import {createProduct} from "@/features/pantry/services/product.service.ts";
import {useProductAdd} from "@/features/pantry/composable/useProductAdd.ts";

const props = defineProps<{
    modelValue: boolean;
    ingredientId: number;
}>();

const emit = defineEmits<{
    (event: "update:modelValue", value: boolean): void;
    (event: "created", value: Product): void;
}>();

const emitFn = (key: string, item: any) => emit(key, item);
const productAdd  = useProductAdd(emitFn);

onMounted(() => {
    productAdd.ingredientId.value = props.ingredientId


    console.log(props.ingredientId)
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

<!--            <div>-->
<!--                <Label for="product-unit-type">Unit Type</Label>-->
<!--                <Select id="product-unit-type" v-model="unitType">-->
<!--                    <option v-for="type in unitTypeOptions" :key="type" :value="type">{{ type }}</option>-->
<!--                </Select>-->
<!--            </div>-->

<!--            <div>-->
<!--                <Label for="product-unit-values">Unit Values</Label>-->
<!--                <Input-->
<!--                    id="product-unit-values"-->
<!--                    v-model="productAdd.form.value.unit_type"-->
<!--                    placeholder="Comma separated e.g. each, pack, g"-->
<!--                />-->
<!--            </div>-->

<!--            <div>-->
<!--                <Label for="product-unit-default">Default Unit</Label>-->
<!--                <Select id="product-unit-default" v-model="unitDefault" :disabled="parsedUnitValues.length === 0">-->
<!--                    <option value="" disabled>Select default unit</option>-->
<!--                    <option v-for="value in parsedUnitValues" :key="value" :value="value">{{ value }}</option>-->
<!--                </Select>-->
<!--            </div>-->

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
