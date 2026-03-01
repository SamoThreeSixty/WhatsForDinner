import type {Product} from "@/features/pantry/types/product.ts";

export interface InventoryItem {
    id: number;
    product_id: number;
    product: Product;
    quantity: number;
    unit: string;
    purchased_at?: string | null;
    expires_at?: string | null;
    category?: string | null;
    location?: string | null;
}

export interface CreateInventoryPayload {
    product_id: number;
    quantity: number;
    unit: string;
    purchased_at?: string | null;
    expires_at?: string | null;
    location?: string | null;
}

export interface InventoryMetadataResponse {
    locations: string[];
}
