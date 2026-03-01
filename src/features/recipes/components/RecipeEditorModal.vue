<script setup lang="ts">
import {watch} from 'vue';
import DialogModal from '@/components/ui/DialogModal.vue';
import RecipeForm from '@/features/recipes/components/RecipeForm.vue';
import {useRecipeEditor} from '@/features/recipes/composable/useRecipeEditor.ts';
import type {Recipe} from '@/features/recipes/types/recipe.ts';

const props = defineProps<{
    modelValue: boolean;
    recipe: Recipe | null;
}>();

const emit = defineEmits<{
    (event: 'update:modelValue', value: boolean): void;
    (event: 'saved', recipe: Recipe): void;
}>();

const editor = useRecipeEditor();

function closeModal() {
    emit('update:modelValue', false);
    editor.resetForm();
}

async function submit() {
    const saved = await editor.submit();
    if (!saved) {
        return;
    }

    emit('saved', saved);
    closeModal();
}

watch(
    () => [props.modelValue, props.recipe] as const,
    ([isOpen, recipe]) => {
        if (!isOpen) {
            editor.resetForm();
            return;
        }

        if (recipe) {
            editor.loadRecipe(recipe);
            return;
        }

        editor.resetForm();
    },
    {immediate: true}
);
</script>

<template>
    <DialogModal
        :model-value="props.modelValue"
        :title="editor.currentRecipeId.value ? 'Edit Recipe' : 'Create Recipe'"
        @update:model-value="(value) => { if (!value) closeModal(); }"
    >
        <RecipeForm
            :form="editor.form.value"
            :loading="editor.loading.value"
            :error="editor.error.value"
            :can-submit="editor.canSubmit.value"
            @submit="submit"
            @cancel="closeModal"
            @add-step="editor.addStep"
            @remove-step="editor.removeStep"
            @add-ingredient="editor.addIngredient"
            @remove-ingredient="editor.removeIngredient"
        />
    </DialogModal>
</template>
