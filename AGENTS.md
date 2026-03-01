# AGENTS.md

This file captures project context and working conventions for agents.

## Snapshot
- Product: Multi-tenant household ingredient tracking.
- Tenancy: Household (users can belong to multiple households).
- Backend: Laravel.
- Frontend: Vue 3.
- Database: PostgreSQL.
- Ingredients: Household inventories reference a global ingredient catalog.
  - Global ingredient has a base name and a grouping hierarchy (e.g., butter -> product level: Lurpak Slightly Salted).
- Tenancy implementation: Single database with `household_id` and global scopes enforcing tenancy.

## Primary Goals
- Maintain a consistent global ingredient catalog with household-specific availability.
- Support multi-household memberships for users.
- Keep ingredient naming and grouping normalized across households.
- Make it easy to see what is in a household at any time.
- Use available ingredients to build meal plans and generate shopping lists for upcoming weeks.

## Curation and Permissions
- Global catalog is community-sourced.
- Users can add custom ingredients and choose whether to submit to global.
- Global submissions are audited by admins, with AI assistance.
- Household roles: Admins can manage membership and settings; base users can do everything else.

## Data Model Notes
- Ingredients should be saved to a product (what was purchased) and linked to a group item.
  - Example: multiple apple products from different suppliers all map to group item "apples".
- Ingredient = base type (salt, butter, bread).
- Product = specific purchased product for an ingredient.
- Duplicates/aliases should be handled separately, with a user option for both.

## Non-Functional Requirements
- Performance and ease of use are key.
- Audit history is not strict; edits/adjustments should be easy.

## Frontend Architecture Rules
- Keep view and presentational components thin.
- Put business logic in composables and services.
- Keep types and mapping helpers in `types` files.

What belongs in components/views:
- Template markup and styling classes.
- Simple UI state only (open/close flags, selected tab, local field bindings).
- Wiring calls to composable methods and emitting UI events.

What must stay out of components/views:
- API calls.
- Debounce/timer logic.
- Validation rules and business branching.
- Data normalization/mapping logic.
- Non-trivial watchers/computed that encode business behavior.

Implementation constraints for agents:
- Default to adding/updating a composable + service + types for new feature behavior.
- In views, avoid inline async logic except trivial one-liners that delegate to composables.
- If a requested change would force heavy view logic, stop and ask before proceeding.
- Do not refactor unrelated files when applying this pattern.

Delivery checklist for agents:
- Provide a short plan before edits mapping responsibilities to files.
- After edits, include a brief split summary:
  - what logic lives in composable/service/type files
  - what remains in the view/component

## Environments
- Development only for now.

## Known Code Pointers
- `app/Models/Household.php`
- `app/Models/HouseholdMembership.php`
- `app/Models/Ingredient.php`
- `app/Models/AbstractTenancyModel.php`
- `app/Models/Scopes/HouseholdTenancyScope.php`
- `app/Support/Tenancy/CurrentHousehold.php`
- `database/migrations/2026_02_24_001617_create_ingredients_table.php`
- `database/migrations/2026_02_25_223000_add_household_id_to_ingredients_table.php`

## Open Questions
1. What is the exact ingredient grouping schema (levels, required fields, and constraints)?
2. Any additional key files (migrations/controllers/services) to align with beyond the ones listed?
