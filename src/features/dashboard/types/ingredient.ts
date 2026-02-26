export type UnitType = 'mass' | 'volume' | 'count';

export interface Ingredient {
    id: number;
    name: string;
    category: string | null;
    location: string | null;
    unit_type: UnitType;
    unit: string;
    quantity: string;
    purchased_at: string | null;
    expires_at: string | null;
    batch_reference: string | null;
    created_at: string;
    updated_at: string;
}

export interface IngredientPayload {
    name: string;
    quantity: number;
    unit_type: UnitType;
    unit: string;
    category?: string | null;
    location?: string | null;
    purchased_at?: string | null;
    expires_at?: string | null;
    batch_reference?: string | null;
}
