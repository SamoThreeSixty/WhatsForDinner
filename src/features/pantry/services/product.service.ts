import {api, type ResourceCollectionResponse} from "@/lib/api.ts";
import type {Product} from "@/features/pantry/types/product.ts";

export async function listProducts(ingredientId: number, search?: string, limit?: number): Promise<Product[]> {
    const response = await api.get('/products', {
        params: {
            ingredient_id: ingredientId,
            inventory_id: ingredientId,
            q: search?.trim() || undefined,
            limit,
        },
    });

    const payload = response.data as Product[] | ResourceCollectionResponse<Product>;

    return Array.isArray(payload) ? payload : payload.data ?? [];
}
