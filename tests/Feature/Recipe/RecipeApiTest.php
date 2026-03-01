<?php

namespace Tests\Feature\Recipe;

use App\Models\Household;
use App\Models\HouseholdMembership;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecipeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_recipe_with_nested_steps_ingredients_and_tags(): void
    {
        [$user, $household] = $this->createApprovedMemberContext();

        $ingredient = Ingredient::query()->create([
            'slug' => 'salt',
            'name' => 'salt',
            'category' => 'pantry',
        ]);

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader('X-Household-Id', (string) $household->id)
            ->postJson('/api/recipes', [
                'title' => 'Roast Potatoes',
                'description' => 'Crispy roast potatoes.',
                'prep_time_minutes' => 10,
                'cook_time_minutes' => 45,
                'servings' => 4,
                'source_type' => 'manual',
                'nutrition' => [
                    'calories' => 420,
                    'protein_g' => 7,
                ],
                'steps' => [
                    ['instruction' => 'Preheat oven to 220C.'],
                    ['instruction' => 'Roast until golden.', 'timer_seconds' => 2400],
                ],
                'ingredients' => [
                    ['ingredient_id' => $ingredient->id, 'amount' => 1.000, 'unit' => 'kg'],
                    ['ingredient_text' => 'black pepper', 'amount' => 1.000, 'unit' => 'tsp', 'is_optional' => true],
                ],
                'tags' => ['Dinner', 'Vegetarian'],
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Roast Potatoes')
            ->assertJsonCount(2, 'data.steps')
            ->assertJsonCount(2, 'data.ingredients')
            ->assertJsonCount(2, 'data.tags');

        $recipeId = (int) $response->json('data.id');

        $this->assertDatabaseHas('recipes', [
            'id' => $recipeId,
            'household_id' => $household->id,
            'created_by_user_id' => $user->id,
            'title' => 'Roast Potatoes',
        ]);

        $this->assertDatabaseHas('recipe_steps', [
            'recipe_id' => $recipeId,
            'position' => 1,
            'instruction' => 'Preheat oven to 220C.',
        ]);

        $this->assertDatabaseHas('recipe_ingredients', [
            'recipe_id' => $recipeId,
            'position' => 1,
            'ingredient_id' => $ingredient->id,
        ]);

        $this->assertDatabaseHas('tags', ['slug' => 'dinner']);
        $this->assertDatabaseHas('tags', ['slug' => 'vegetarian']);
    }

    public function test_store_requires_each_ingredient_row_to_have_identifier_or_text(): void
    {
        [$user, $household] = $this->createApprovedMemberContext();

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader('X-Household-Id', (string) $household->id)
            ->postJson('/api/recipes', [
                'title' => 'Invalid Recipe',
                'steps' => [
                    ['instruction' => 'Do a thing.'],
                ],
                'ingredients' => [
                    ['amount' => 1, 'unit' => 'g'],
                ],
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ingredients.0']);
    }

    public function test_update_replaces_steps_ingredients_and_tags(): void
    {
        [$user, $household] = $this->createApprovedMemberContext();

        $ingredient = Ingredient::query()->create([
            'slug' => 'salt',
            'name' => 'salt',
            'category' => 'pantry',
        ]);

        $recipe = new Recipe();
        $recipe->household_id = $household->id;
        $recipe->created_by_user_id = $user->id;
        $recipe->title = 'Old Title';
        $recipe->source_type = 'manual';
        $recipe->save();

        $recipe->steps()->create(['position' => 1, 'instruction' => 'Old step']);
        $recipe->ingredients()->create(['position' => 1, 'ingredient_text' => 'old ingredient']);

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader('X-Household-Id', (string) $household->id)
            ->putJson('/api/recipes/'.$recipe->id, [
                'title' => 'New Title',
                'description' => 'Updated recipe description.',
                'prep_time_minutes' => 5,
                'cook_time_minutes' => 20,
                'servings' => 2,
                'source_type' => 'manual',
                'steps' => [
                    ['instruction' => 'New step one'],
                    ['instruction' => 'New step two'],
                ],
                'ingredients' => [
                    ['ingredient_id' => $ingredient->id, 'amount' => 2, 'unit' => 'g'],
                ],
                'tags' => ['Quick'],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'New Title')
            ->assertJsonCount(2, 'data.steps')
            ->assertJsonCount(1, 'data.ingredients')
            ->assertJsonCount(1, 'data.tags');

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'title' => 'New Title',
            'cook_time_minutes' => 20,
        ]);

        $this->assertDatabaseCount('recipe_steps', 2);
        $this->assertDatabaseCount('recipe_ingredients', 1);
        $this->assertDatabaseHas('tags', ['slug' => 'quick']);
    }

    public function test_recipe_queries_are_scoped_to_active_household_context(): void
    {
        $user = User::factory()->create();
        $householdA = Household::query()->create([
            'name' => 'Home A',
            'slug' => 'home-a',
            'locale' => 'en',
            'currency' => 'GBP',
            'new_members' => true,
        ]);
        $householdB = Household::query()->create([
            'name' => 'Home B',
            'slug' => 'home-b',
            'locale' => 'en',
            'currency' => 'GBP',
            'new_members' => true,
        ]);

        HouseholdMembership::query()->create([
            'household_id' => $householdA->id,
            'user_id' => $user->id,
            'role' => HouseholdMembership::ROLE_OWNER,
            'status' => HouseholdMembership::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        HouseholdMembership::query()->create([
            'household_id' => $householdB->id,
            'user_id' => $user->id,
            'role' => HouseholdMembership::ROLE_OWNER,
            'status' => HouseholdMembership::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $recipeA = new Recipe();
        $recipeA->household_id = $householdA->id;
        $recipeA->created_by_user_id = $user->id;
        $recipeA->title = 'A Recipe';
        $recipeA->source_type = 'manual';
        $recipeA->save();

        $recipeB = new Recipe();
        $recipeB->household_id = $householdB->id;
        $recipeB->created_by_user_id = $user->id;
        $recipeB->title = 'B Recipe';
        $recipeB->source_type = 'manual';
        $recipeB->save();

        Sanctum::actingAs($user);

        $index = $this
            ->withHeader('X-Household-Id', (string) $householdA->id)
            ->getJson('/api/recipes');

        $index
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $recipeA->id);

        $this
            ->withHeader('X-Household-Id', (string) $householdA->id)
            ->getJson('/api/recipes/'.$recipeB->id)
            ->assertNotFound();
    }

    public function test_recipe_index_can_filter_by_multiple_tags_array(): void
    {
        [$user, $household] = $this->createApprovedMemberContext();

        $quickOnly = new Recipe();
        $quickOnly->household_id = $household->id;
        $quickOnly->created_by_user_id = $user->id;
        $quickOnly->title = 'Quick Soup';
        $quickOnly->source_type = 'manual';
        $quickOnly->save();
        $quickOnly->tags()->sync($this->tagIdsFromNames(['Quick']));

        $dinnerOnly = new Recipe();
        $dinnerOnly->household_id = $household->id;
        $dinnerOnly->created_by_user_id = $user->id;
        $dinnerOnly->title = 'Dinner Pie';
        $dinnerOnly->source_type = 'manual';
        $dinnerOnly->save();
        $dinnerOnly->tags()->sync($this->tagIdsFromNames(['Dinner']));

        $other = new Recipe();
        $other->household_id = $household->id;
        $other->created_by_user_id = $user->id;
        $other->title = 'Breakfast Oats';
        $other->source_type = 'manual';
        $other->save();
        $other->tags()->sync($this->tagIdsFromNames(['Breakfast']));

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader('X-Household-Id', (string) $household->id)
            ->getJson('/api/recipes?tags[]=quick&tags[]=dinner');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->all();

        $this->assertContains($quickOnly->id, $ids);
        $this->assertContains($dinnerOnly->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_tags_index_returns_search_results_sorted_by_name(): void
    {
        [$user, $household] = $this->createApprovedMemberContext();

        $recipeA = new Recipe();
        $recipeA->household_id = $household->id;
        $recipeA->created_by_user_id = $user->id;
        $recipeA->title = 'A Recipe';
        $recipeA->source_type = 'manual';
        $recipeA->save();
        $recipeA->tags()->sync($this->tagIdsFromNames(['Vegan']));

        $recipeB = new Recipe();
        $recipeB->household_id = $household->id;
        $recipeB->created_by_user_id = $user->id;
        $recipeB->title = 'B Recipe';
        $recipeB->source_type = 'manual';
        $recipeB->save();
        $recipeB->tags()->sync($this->tagIdsFromNames(['Weeknight', 'Vegetarian']));

        Sanctum::actingAs($user);

        $response = $this
            ->withHeader('X-Household-Id', (string) $household->id)
            ->getJson('/api/tags?q=veg');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'vegan')
            ->assertJsonPath('data.1.slug', 'vegetarian');
    }

    /**
     * @param array<int, string> $names
     * @return array<int, int>
     */
    private function tagIdsFromNames(array $names): array
    {
        return collect($names)
            ->map(function (string $name): int {
                $slug = Str::slug($name);

                return (int) Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                )->id;
            })
            ->all();
    }

    /**
     * @return array{0:User,1:Household}
     */
    private function createApprovedMemberContext(): array
    {
        $user = User::factory()->create();
        $household = Household::query()->create([
            'name' => 'Test Household',
            'slug' => 'test-household-'.uniqid(),
            'locale' => 'en',
            'currency' => 'GBP',
            'new_members' => true,
        ]);

        HouseholdMembership::query()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
            'role' => HouseholdMembership::ROLE_OWNER,
            'status' => HouseholdMembership::STATUS_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return [$user, $household];
    }
}
