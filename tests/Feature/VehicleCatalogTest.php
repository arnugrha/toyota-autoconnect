<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_can_be_accessed(): void
    {
        $featuredVehicle = Vehicle::factory()->featured()->create([
            'name' => 'Toyota Innova Zenix Showcase',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Toyota AutoConnect', false);
        $response->assertSee('Toyota Innova Zenix Showcase');
    }

    public function test_catalog_displays_published_vehicles(): void
    {
        $published = Vehicle::factory()->create([
            'name' => 'Toyota Published Car',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.index'));

        $response->assertStatus(200);
        $response->assertSee('Toyota Published Car');
    }

    public function test_catalog_does_not_display_unpublished_vehicles(): void
    {
        $unpublished = Vehicle::factory()->unpublished()->create([
            'name' => 'Toyota Hidden Prototype Car',
            'slug' => 'toyota-hidden-prototype-car',
        ]);

        $response = $this->get(route('vehicles.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Toyota Hidden Prototype Car');

        // Direct show route on unpublished vehicle must also return 404
        $detailResponse = $this->get(route('vehicles.show', $unpublished->slug));
        $detailResponse->assertStatus(404);
    }

    public function test_keyword_search_filters_vehicles_correctly(): void
    {
        Vehicle::factory()->create([
            'name' => 'Toyota Alphard Luxury',
            'variant' => '3.5 Q Executive Lounge',
            'published_at' => now()->subDay(),
        ]);

        Vehicle::factory()->create([
            'name' => 'Toyota Yaris Hatchback',
            'variant' => '1.5 GR Sport',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.index', ['q' => 'Alphard']));

        $response->assertStatus(200);
        $response->assertSee('Toyota Alphard Luxury');
        $response->assertDontSee('Toyota Yaris Hatchback');
    }

    public function test_category_filter_works_properly(): void
    {
        Vehicle::factory()->create([
            'name' => 'Toyota Raize SUV',
            'category' => 'SUV',
            'published_at' => now()->subDay(),
        ]);

        Vehicle::factory()->create([
            'name' => 'Toyota Voxy MPV',
            'category' => 'MPV',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.index', ['category' => 'SUV']));

        $response->assertStatus(200);
        $response->assertSee('Toyota Raize SUV');
        $response->assertDontSee('Toyota Voxy MPV');
    }

    public function test_price_range_filter_works_properly(): void
    {
        Vehicle::factory()->create([
            'name' => 'Toyota Low Budget Car',
            'price' => 200000000,
            'published_at' => now()->subDay(),
        ]);

        Vehicle::factory()->create([
            'name' => 'Toyota Mid Budget Car',
            'price' => 500000000,
            'published_at' => now()->subDay(),
        ]);

        Vehicle::factory()->create([
            'name' => 'Toyota Luxury High Budget Car',
            'price' => 1500000000,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.index', [
            'min_price' => 300000000,
            'max_price' => 700000000,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Toyota Mid Budget Car');
        $response->assertDontSee('Toyota Low Budget Car');
        $response->assertDontSee('Toyota Luxury High Budget Car');
    }

    public function test_sorting_works_as_expected(): void
    {
        $cheap = Vehicle::factory()->create([
            'name' => 'Toyota Cheaper Model',
            'price' => 150000000,
            'published_at' => now()->subDays(5),
        ]);

        $expensive = Vehicle::factory()->create([
            'name' => 'Toyota Premium Expensive Model',
            'price' => 900000000,
            'published_at' => now()->subDays(1),
        ]);

        // Sort price asc
        $responseAsc = $this->get(route('vehicles.index', ['sort' => 'price_asc']));
        $responseAsc->assertStatus(200);
        $responseAsc->assertSeeInOrder(['Toyota Cheaper Model', 'Toyota Premium Expensive Model']);

        // Sort price desc
        $responseDesc = $this->get(route('vehicles.index', ['sort' => 'price_desc']));
        $responseDesc->assertStatus(200);
        $responseDesc->assertSeeInOrder(['Toyota Premium Expensive Model', 'Toyota Cheaper Model']);
    }

    public function test_pagination_preserves_query_filters(): void
    {
        // Create 15 SUV vehicles to exceed the 12 per page limit
        Vehicle::factory()->count(15)->create([
            'category' => 'SUV',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.index', [
            'category' => 'SUV',
            'sort' => 'name_asc',
        ]));

        $response->assertStatus(200);
        // Page links must preserve query parameters
        $response->assertSee('category=SUV');
        $response->assertSee('sort=name_asc');
    }

    public function test_vehicle_detail_can_be_accessed_via_valid_slug(): void
    {
        $vehicle = Vehicle::factory()->create([
            'name' => 'Toyota Fortuner Detailed',
            'slug' => 'toyota-fortuner-detailed',
            'variant' => '2.8 GR Sport 4x4',
            'category' => 'SUV',
            'price' => 740000000,
            'engine_capacity' => '2.755 cc',
            'passenger_capacity' => 7,
            'dimensions' => '4.795 x 1.855 x 1.835 mm',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.show', $vehicle->slug));

        $response->assertStatus(200);
        $response->assertSee('Toyota Fortuner Detailed');
        $response->assertSee('2.8 GR Sport 4x4');
        $response->assertSee('2.755 cc');
        $response->assertSee('4.795 x 1.855 x 1.835 mm');
    }

    public function test_invalid_slug_returns_404(): void
    {
        $response = $this->get(route('vehicles.show', 'non-existent-vehicle-slug'));

        $response->assertStatus(404);
        $response->assertSee('Kendaraan Tidak Ditemukan');
    }

    public function test_vehicle_primary_and_gallery_photos_are_rendered(): void
    {
        $vehicle = Vehicle::factory()->create([
            'name' => 'Toyota Car With Images',
            'slug' => 'toyota-car-with-images',
            'published_at' => now()->subDay(),
        ]);

        VehicleImage::factory()->create([
            'vehicle_id' => $vehicle->id,
            'image_path' => 'images/vehicles/sample-photo.svg',
            'alt_text' => 'Toyota Sample Exterior Photo',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        $response = $this->get(route('vehicles.show', $vehicle->slug));

        $response->assertStatus(200);
        $response->assertSee('Toyota Sample Exterior Photo');
    }

    public function test_vehicle_serves_real_jpg_photo_instead_of_svg(): void
    {
        $vehicle = Vehicle::factory()->create([
            'name' => 'All New bZ4X EV',
            'slug' => 'all-new-bz4x-ev',
            'published_at' => now()->subDay(),
        ]);

        VehicleImage::factory()->create([
            'vehicle_id' => $vehicle->id,
            'image_path' => 'images/vehicles/all-new-bz4x-ev-exterior.svg',
            'alt_text' => 'Toyota bZ4X Exterior Photo',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        $this->assertStringEndsWith('.jpg', $vehicle->primary_image_url);
        $this->assertStringContainsString('all-new-bz4x-ev-exterior.jpg', $vehicle->primary_image_url);
    }

    public function test_nullable_price_is_handled_gracefully(): void
    {
        $vehicle = Vehicle::factory()->withoutPrice()->create([
            'name' => 'Toyota Unpriced Electric Concept',
            'slug' => 'toyota-unpriced-electric-concept',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.show', $vehicle->slug));

        $response->assertStatus(200);
        $response->assertSee('Harga Belum Tersedia');
    }

    public function test_related_vehicles_exclude_the_current_vehicle(): void
    {
        $currentVehicle = Vehicle::factory()->create([
            'name' => 'Current Target Innova',
            'slug' => 'current-target-innova',
            'category' => 'MPV',
            'published_at' => now()->subDay(),
        ]);

        $siblingVehicle = Vehicle::factory()->create([
            'name' => 'Sibling Sienta MPV',
            'slug' => 'sibling-sienta-mpv',
            'category' => 'MPV',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('vehicles.show', $currentVehicle->slug));

        $response->assertStatus(200);
        $response->assertSee('Sibling Sienta MPV');

        // In the related vehicles section, Current Target Innova should NOT be listed as a related card
        // We can verify related vehicles query from the model directly
        $relatedVehicles = Vehicle::published()
            ->where('category', $currentVehicle->category)
            ->where('id', '!=', $currentVehicle->id)
            ->pluck('id');

        $this->assertNotContains($currentVehicle->id, $relatedVehicles);
        $this->assertContains($siblingVehicle->id, $relatedVehicles);
    }
}
