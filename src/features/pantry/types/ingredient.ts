import type {SearchOption} from "@/components/ui/SearchSelection.vue";

export interface Ingredient {
    id: number,
    name: string
}

export type IngredientOption = SearchOption;

export function toIngredientOption(item: Ingredient): IngredientOption {
    return {
        label: item.name,
        value: item.id
    }
}

export function toIngredientOptions(items: Ingredient[]): IngredientOption[] {
    return Array.isArray(items) ? items.map(toIngredientOption) : [];
}
