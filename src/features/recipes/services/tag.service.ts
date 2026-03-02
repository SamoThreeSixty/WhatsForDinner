import {api, type ResourceCollectionResponse} from "@/lib/api.ts";
import type {Tag} from "@/features/recipes/types/tag.ts";

export async function listTags(search?: string, limit?: number): Promise<Tag[]> {
    const response = await api.get('/tags', {
        params: {
            q: search?.trim() || undefined,
            limit,
        },
    });

    const payload = response.data as Tag[] | ResourceCollectionResponse<Tag>;
    return Array.isArray(payload) ? payload : payload.data ?? [];
}
