import {api, type ResourceCollectionResponse, type ResourceItemResponse} from "@/lib/api.ts";
import type {
    CreateInventoryPayload,
    InventoryItem,
    InventoryMetadataResponse
} from "@/features/pantry/types/inventry.ts";

export async function listInventoryItems(search?: string, limit?: number): Promise<InventoryItem[]> {
    const response = await api.get('/inventory-items', {
        params: {
            q: search?.trim() || undefined,
            limit,
        },
    });

    const payload = response.data as InventoryItem[] | ResourceCollectionResponse<InventoryItem>;
    return Array.isArray(payload) ? payload : payload.data ?? [];
}

export async function createInventoryItem(payload: CreateInventoryPayload): Promise<InventoryItem> {
    const response = await api.post('/inventory-items', payload);
    const body = response.data as InventoryItem | ResourceItemResponse<InventoryItem>;

    return 'data' in body ? body.data : body;
}

export async function getInventoryMetadata(): Promise<InventoryMetadataResponse> {
    const response = await api.get('/inventory-items/metadata');
    return response.data as InventoryMetadataResponse;
}
