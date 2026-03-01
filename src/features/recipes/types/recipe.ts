export type RecipeSourceType = 'manual' | 'site_import' | 'ai_generated';

export interface RecipeTag {
    id: number;
    slug: string;
    name: string;
    created_at?: string;
    updated_at?: string;
}

export interface RecipeStep {
    id: number;
    recipe_id: number;
    position: number;
    instruction: string;
    timer_seconds?: number | null;
    created_at?: string;
    updated_at?: string;
}

export interface RecipeIngredient {
    id: number;
    recipe_id: number;
    position: number;
    ingredient_id?: number | null;
    ingredient_slug?: string | null;
    ingredient_name?: string | null;
    ingredient_text?: string | null;
    amount?: number | null;
    unit?: string | null;
    preparation_note?: string | null;
    is_optional: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface Recipe {
    id: number;
    household_id: number;
    created_by_user_id: number;
    title: string;
    description?: string | null;
    prep_time_minutes?: number | null;
    cook_time_minutes?: number | null;
    servings?: number | null;
    source_type: RecipeSourceType;
    source_url?: string | null;
    nutrition: Record<string, unknown>;
    tags?: RecipeTag[];
    steps?: RecipeStep[];
    ingredients?: RecipeIngredient[];
    created_at?: string;
    updated_at?: string;
}

export interface RecipeStepInput {
    position?: number;
    instruction: string;
    timer_seconds?: number | null;
}

export interface RecipeIngredientInput {
    position?: number;
    ingredient_id?: number | null;
    ingredient_slug?: string | null;
    ingredient_text?: string | null;
    amount?: number | null;
    unit?: string | null;
    preparation_note?: string | null;
    is_optional?: boolean;
}

export interface RecipeWritePayload {
    title: string;
    description?: string | null;
    prep_time_minutes?: number | null;
    cook_time_minutes?: number | null;
    servings?: number | null;
    source_type?: RecipeSourceType;
    source_url?: string | null;
    nutrition?: Record<string, unknown>;
    steps: RecipeStepInput[];
    ingredients: RecipeIngredientInput[];
    tags?: string[];
}

export interface RecipeListParams {
    q?: string;
    tag?: string;
    ingredient_id?: number;
    ingredient_slug?: string;
    max_cook_time?: number;
    source_type?: RecipeSourceType;
    limit?: number;
}
