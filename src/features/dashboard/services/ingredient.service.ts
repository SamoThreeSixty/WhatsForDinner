import {api} from '@/lib/api';
import type {Ingredient, IngredientPayload} from '@/features/dashboard/types/ingredient';

interface CollectionResponse<T> {
    data: T[];
}

interface ItemResponse<T> {
    data: T;
}

export async function listIngredients(search?: string, limit?: number) {
    const response = await api.get<CollectionResponse<Ingredient>>('/ingredients', {
        params: {
            q: search?.trim() || undefined,
            limit,
        },
    });

    return response.data.data;
}

export async function createIngredient(payload: IngredientPayload) {
    const response = await api.post<ItemResponse<Ingredient>>('/ingredients', payload);
    return response.data.data;
}

export async function updateIngredient(id: number, payload: IngredientPayload) {
    const response = await api.put<ItemResponse<Ingredient>>(`/ingredients/${id}`, payload);
    return response.data.data;
}

export async function deleteIngredient(id: number) {
    await api.delete(`/ingredients/${id}`);
}
