import type {SearchOption} from "@/components/ui/SearchSelection.vue";
import type {UnitType} from "@/features/pantry/types/unit.ts";

export interface Product {
    id: number;
    ingredient_id: number;
    company?: string | null;
    name: string;
    unit_type?: string | null;
    unit_options?: string[];
    unit_default?: string | null;
}

export interface AddProduct {
    company: string;
    name: string;
    // unit_type: string;
}

export interface CreateProductPayload {
    ingredient_id: number;
    name: string;
    // unit_type: UnitType;
    // unit_options: string[];
    // unit_default: string;
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
