<?php

namespace Tests\Feature;

use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Models\TouristSite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TouristCategoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function category(): TouristCategory
    {
        return TouristCategory::create(['name' => 'Plages test', 'slug' => 'plages-test', 'is_active' => 1, 'hero_image_url' => '/storage/tourist/categories/hero_test.jpg']);
    }

    public function test_a_category_used_by_sites_is_not_deleted_and_keeps_its_images(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();
        $city = TouristCity::create(['name' => 'Ville test', 'slug' => 'ville-test', 'is_active' => 1]);
        TouristSite::create(['city_id' => $city->id, 'category_id' => $category->id, 'name' => 'Site test', 'slug' => 'site-test', 'is_active' => 1]);

        $this->actingAs($admin)
            ->from(route('admin.tourist.categories.index'))
            ->delete(route('admin.tourist.categories.destroy', $category))
            ->assertRedirect(route('admin.tourist.categories.index'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, '1 site touristique'));

        $this->assertDatabaseHas('tourist_categories', ['id' => $category->id, 'hero_image_url' => '/storage/tourist/categories/hero_test.jpg']);
    }

    public function test_a_city_with_sites_is_not_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();
        $city = TouristCity::create(['name' => 'Ville test', 'slug' => 'ville-test', 'is_active' => 1]);
        TouristSite::create(['city_id' => $city->id, 'category_id' => $category->id, 'name' => 'Site test', 'slug' => 'site-test', 'is_active' => 1]);

        $this->actingAs($admin)
            ->delete(route('admin.tourist.cities.destroy', $city))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tourist_cities', ['id' => $city->id]);
        $this->assertDatabaseHas('tourist_sites', ['slug' => 'site-test']);
    }

    public function test_the_hero_image_can_be_removed_when_editing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();

        $this->actingAs($admin)
            ->put(route('admin.tourist.categories.update', $category), ['name' => 'Plages test', 'remove_hero_image' => '1', 'is_active' => '1'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNull($category->fresh()->hero_image_url);
    }

    public function test_an_unused_category_is_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->category();

        $this->actingAs($admin)
            ->delete(route('admin.tourist.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tourist_categories', ['id' => $category->id]);
    }
}
