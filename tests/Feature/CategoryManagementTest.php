<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cannot_access_category_management(): void
    {
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
        $this->get(route('admin.categories.create'))->assertRedirect(route('login'));
        $this->post(route('admin.categories.store'), $this->formData())->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_category_list_and_form(): void
    {
        $user = User::factory()->create();
        Category::query()->create($this->attributes());

        $this->actingAs($user)->get(route('admin.categories.index'))
            ->assertOk()->assertSee('Game Development')->assertSee('NUEVA CATEGORÍA');
        $this->get(route('admin.categories.create'))->assertOk()->assertSee('Crear categoría');
    }

    public function test_user_can_create_update_activate_and_deactivate_category(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.categories.store'), $this->formData())
            ->assertRedirect(route('admin.categories.index'))->assertSessionHas('status');
        $category = Category::query()->firstOrFail();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'slug' => 'game-development', 'is_active' => true]);

        $this->put(route('admin.categories.update', $category), [...$this->formData(), 'name' => 'Updated category', 'slug' => 'updated-category'])
            ->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated category', 'slug' => 'updated-category']);

        $this->patch(route('admin.categories.toggle', $category))->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => false]);
        $this->patch(route('admin.categories.toggle', $category))->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'is_active' => true]);
    }

    public function test_slug_must_be_unique_when_creating_and_updating(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('admin.categories.store'), $this->formData())->assertRedirect();
        $category = Category::query()->firstOrFail();

        $this->from(route('admin.categories.create'))
            ->post(route('admin.categories.store'), [...$this->formData(), 'name' => 'Different category'])
            ->assertRedirect(route('admin.categories.create'))->assertSessionHasErrors('slug');

        $other = Category::query()->create([...$this->attributes(), 'name' => 'Other category', 'slug' => 'other-category']);
        $this->from(route('admin.categories.edit', $other))
            ->put(route('admin.categories.update', $other), [...$this->formData(), 'slug' => $category->slug])
            ->assertRedirect(route('admin.categories.edit', $other))->assertSessionHasErrors('slug');

        // A category may keep its own slug during an update.
        $this->put(route('admin.categories.update', $category), $this->formData())->assertRedirect(route('admin.categories.index'));
    }

    public function test_category_policy_authorizes_authenticated_admin_actions(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create($this->attributes());

        $this->assertTrue(Gate::forUser($user)->allows('viewAny', Category::class));
        $this->assertTrue(Gate::forUser($user)->allows('create', Category::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $category));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $category));
    }

    public function test_delete_is_logical_and_preserves_the_category_row(): void
    {
        $category = Category::query()->create($this->attributes());

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'slug' => 'game-development']);
        $this->assertNull(Category::query()->find($category->id));
        $this->assertNotNull(Category::withTrashed()->find($category->id));
    }

    public function test_category_values_are_escaped_in_the_admin_list(): void
    {
        $category = Category::query()->create([
            ...$this->attributes(),
            'name' => '<script>alert(1)</script>',
            'slug' => 'escaped-category',
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $this->actingAs(User::factory()->create())->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'name' => 'Game Development',
            'slug' => 'game-development',
            'description' => 'Development topics and studio updates.',
            'is_active' => '1',
        ];
    }

    /** @return array<string, mixed> */
    private function attributes(): array
    {
        return [...$this->formData(), 'is_active' => true];
    }
}
