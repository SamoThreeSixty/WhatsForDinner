import {api, type ResourceCollectionResponse, type ResourceItemResponse} from "@/lib/api.ts";
import type {CreateProductPayload, Product, ProductMetadataResponse} from "@/features/pantry/types/product.ts";

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

export async function createProduct(payload: CreateProductPayload): Promise<Product> {
    const response = await api.post('/products', payload);
    const body = response.data as Product | ResourceItemResponse<Product>;

    return 'data' in body ? body.data : body;
}

export async function getProductMetadata(): Promise<ProductMetadataResponse> {
    const response = await api.get('/products/metadata');
    return response.data as ProductMetadataResponse;
}
