import type {SearchOption} from "@/components/ui/SearchSelection.vue";

export interface Product {
    id: number;
    ingredient_id: number;
    company?: string | null;
    name: string;
    unit_type?: string | null;
    unit_options?: string[];
    unit_default?: string | null;
}

export type ProductOption = SearchOption;

export function toProductOption(item: Product): ProductOption {
    return {
        label: item.name,
        value: item.id,
    };
}

export function toProductOptions(items: Product[]): ProductOption[] {
    return Array.isArray(items) ? items.map(toProductOption) : [];
}
