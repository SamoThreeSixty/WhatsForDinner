# Recipes API

This document defines the current recipe API behavior for both frontend engineers and AI agents.

## Base

- Base path: `/api`
- Auth: `auth:sanctum` + verified + household context
- Household scoping: all recipe data is scoped by `X-Household-Id`

## Endpoints

### `GET /recipes`

Returns a paginated recipe list.

Query params:

- `q` `string|max:100` free-text search on title/description (case-insensitive)
- `tag` `string|max:255` legacy single tag filter
- `tags[]` `array|max:25` multi-tag filter (slug/name input, normalized to slug)
- `ingredient_id` `integer` existing ingredient id
- `ingredient_slug` `string|max:255` existing ingredient slug
- `max_cook_time` `integer|min:1|max:1440`
- `source_type` one of `manual|site_import|ai_generated`
- `per_page` `integer|min:1|max:100` default `20`
- `page` `integer|min:1` default `1`

Response shape:

```json
{
  "data": [
    {
      "id": 12,
      "title": "Roast Potatoes",
      "source_type": "manual",
      "tags": [],
      "steps": [],
      "ingredients": []
    }
  ],
  "links": {
    "first": "http://.../api/recipes?page=1",
    "last": "http://.../api/recipes?page=4",
    "prev": null,
    "next": "http://.../api/recipes?page=2"
  },
  "meta": {
    "current_page": 1,
    "last_page": 4,
    "per_page": 20,
    "total": 68
  }
}
```

### `POST /recipes`

Creates a recipe for the active household.

Required payload fields:

- `title`
- `steps` (min 1, max 100)
- `ingredients` (min 1, max 200)

Validation highlights:

- `title`: `string|max:255`
- `description`: `string|max:4000`
- `prep_time_minutes` / `cook_time_minutes`: `integer|min:0|max:1440`
- `servings`: `integer|min:1|max:100`
- `source_url`: valid URL, `max:2048`
- `nutrition`: JSON object/array
- `steps.*.instruction`: `string|min:2|max:1000`
- `steps.*.timer_seconds`: `integer|min:1|max:86400`
- `ingredients.*` requires at least one of:
  - `ingredient_id`
  - `ingredient_slug`
  - `ingredient_text`
- `ingredients.*.amount`: `numeric|min:0|max:1000000`
- `ingredients.*.unit`: `string|max:32`
- `ingredients.*.preparation_note`: `string|max:512`
- `tags`: `array|max:25`
- `tags.*`: `string|min:1|max:64|distinct:ignore_case`

### `PUT /recipes/{recipe}`

Same validation and shape as `POST /recipes`; replaces recipe fields and syncs steps/ingredients/tags.

### `GET /recipes/{recipe}`

Returns one scoped recipe (404 if recipe is outside active household).

### `DELETE /recipes/{recipe}`

Deletes one scoped recipe.

### `GET /tags`

Returns recipe tag options for UI selectors.

Query params:

- `q` `string|max:100` case-insensitive search by tag name
- `limit` `integer|min:1|max:100` default `50`

## Frontend Integration Notes

- Recipe list requests should send `tags[]` for multi-select filter values.
- Recipe list pagination uses `page` + `per_page`.
- UI should read `meta.current_page`, `meta.last_page`, and `meta.total` from the response.
- Keep editor tag input and filter tag input independent:
  - Editor sends free-form `tags` strings.
  - Filter uses known tag options from `/tags`.

## AI Agent Notes

- Always include `X-Household-Id` in recipe/tag requests for scoped behavior.
- Prefer `tags[]` over `tag` when multiple values are available.
- For deterministic tests, assert both `data` and `meta` pagination keys.
- If search behavior changes, confirm case-insensitive matching still works for both:
  - `GET /recipes?q=...`
  - `GET /tags?q=...`

