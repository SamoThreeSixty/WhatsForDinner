import {api} from '@/lib/api';
import type {Pantry, PantryPayload} from '@/features/pantry/types/pantry.ts';

interface CollectionResponse<T> {
    data: T[];
}

interface ItemResponse<T> {
    data: T;
}

export async function listIngredients(search?: string, limit?: number) {
    const response = await api.get<CollectionResponse<Pantry>>('/ingredients', {
        params: {
            q: search?.trim() || undefined,
            limit,
        },
    });

    return response.data.data;
}

export async function createIngredient(payload: PantryPayload) {
    const response = await api.post<ItemResponse<Pantry>>('/ingredients', payload);
    return response.data.data;
}

export async function updateIngredient(id: number, payload: PantryPayload) {
    const response = await api.put<ItemResponse<Pantry>>(`/ingredients/${id}`, payload);
    return response.data.data;
}

export async function deleteIngredient(id: number) {
    await api.delete(`/ingredients/${id}`);
}
