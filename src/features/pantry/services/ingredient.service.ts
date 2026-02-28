import {api, type ResourceCollectionResponse} from "@/lib/api.ts";
import type {Ingredient} from "@/features/pantry/types/ingredient.ts";

export async function listIngredients(search?: string, limit?: number): Promise<Ingredient[]> {
    const response = await api.get('/ingredients', {
        params: {
            q: search?.trim() || undefined,
            limit,
        },
    });

    const payload = response.data as ResourceCollectionResponse<Ingredient>;
    return payload.data;
}
