import {api, type ResourceCollectionResponse, type ResourceItemResponse} from "@/lib/api.ts";
import type {Recipe, RecipeListParams, RecipeWritePayload} from "@/features/recipes/types/recipe.ts";

function toListParams(params: RecipeListParams) {
    return {
        q: params.q?.trim() || undefined,
        tag: params.tag?.trim() || undefined,
        ingredient_id: params.ingredient_id,
        ingredient_slug: params.ingredient_slug?.trim() || undefined,
        max_cook_time: params.max_cook_time,
        source_type: params.source_type,
        limit: params.limit,
    };
}

function fromItemResponse(body: Recipe | ResourceItemResponse<Recipe>): Recipe {
    return 'data' in body ? body.data : body;
}

export async function listRecipes(params: RecipeListParams = {}): Promise<Recipe[]> {
    const response = await api.get('/recipes', {
        params: toListParams(params),
    });

    const payload = response.data as Recipe[] | ResourceCollectionResponse<Recipe>;
    return Array.isArray(payload) ? payload : payload.data ?? [];
}

export async function getRecipe(recipeId: number): Promise<Recipe> {
    const response = await api.get(`/recipes/${recipeId}`);
    const body = response.data as Recipe | ResourceItemResponse<Recipe>;

    return fromItemResponse(body);
}

export async function createRecipe(payload: RecipeWritePayload): Promise<Recipe> {
    const response = await api.post('/recipes', payload);
    const body = response.data as Recipe | ResourceItemResponse<Recipe>;

    return fromItemResponse(body);
}

export async function updateRecipe(recipeId: number, payload: RecipeWritePayload): Promise<Recipe> {
    const response = await api.put(`/recipes/${recipeId}`, payload);
    const body = response.data as Recipe | ResourceItemResponse<Recipe>;

    return fromItemResponse(body);
}

export async function deleteRecipe(recipeId: number): Promise<void> {
    await api.delete(`/recipes/${recipeId}`);
}
