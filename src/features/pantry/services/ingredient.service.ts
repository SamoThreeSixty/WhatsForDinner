import {api} from "@/lib/api.ts";
import type {Ingredient} from "@/features/pantry/types/ingredient.ts";

export async function listIngredients(search?: string, limit?: number): Promise<Ingredient[]> {
    const response = await api.get('/ingredients', {
        params: {
            q: search?.trim() || undefined,
            limit,
        },
    });

    return response.data;
}
