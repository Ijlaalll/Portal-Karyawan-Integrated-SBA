<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkVariant;
use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtkManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $ga;
    protected User $user;
    protected User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        $this->ga = User::factory()->create(['role' => 'ga']);
        $this->ga->modules()->create(['module' => 'atk']);

        $this->user = User::factory()->create(['role' => 'user']);
        $this->user->modules()->create(['module' => 'atk']);

        $this->unauthorizedUser = User::factory()->create(['role' => 'user']);
        // No module access
    }

    /** @test */
    public function admin_can_crud_atk_category_item_and_variant()
    {
        $this->actingAs($this->admin);

        // --- 1. Category CRUD ---
        $response = $this->post(route('atk.categories.store'), [
            'name' => 'Kertas & Tinta',
            'description' => 'Semua jenis kertas dan tinta printer',
            'is_active' => 1
        ]);
        $response->assertRedirect(route('atk.categories.index'));
        $this->assertDatabaseHas('atk_categories', ['name' => 'Kertas & Tinta']);

        $category = AtkCategory::first();

        $response = $this->put(route('atk.categories.update', $category), [
            'name' => 'Kertas Dan Tinta',
            'description' => 'Updated desc',
            'is_active' => 1
        ]);
        $response->assertRedirect(route('atk.categories.index'));
        $this->assertDatabaseHas('atk_categories', ['name' => 'Kertas Dan Tinta']);

        // --- 2. Item CRUD ---
        $response = $this->post(route('atk.items.store'), [
            'category_id' => $category->id,
            'name' => 'Kertas A4 80gr',
            'description' => 'Kertas A4 80gr PaperOne',
            'is_active' => 1,
            'variants' => [
                [
                    'name' => 'Default',
                    'unit' => 'Rim',
                    'purchase_price' => 12000,
                    'stock' => 0,
                    'minimum_stock' => 5,
                    'is_active' => 1,
                ]
            ]
        ]);
        $this->assertDatabaseHas('atk_items', ['name' => 'Kertas A4 80gr']);
        $item = AtkItem::first();
        $response->assertRedirect(route('atk.items.show', $item->id));

        // --- 3. Variant CRUD ---
        // Create variant
        $response = $this->post(route('atk.variants.store'), [
            'item_id' => $item->id,
            'name' => 'Putih',
            'unit' => 'Rim',
            'purchase_price' => 15000,
            'stock' => 50,
            'minimum_stock' => 10,
            'is_active' => 1
        ]);
        $response->assertRedirect(route('atk.items.show', $item->id));
        
        // Assert SKU generated starting with ATK-00002 (since Default got ATK-00001)
        $this->assertDatabaseHas('atk_variants', [
            'name' => 'Putih',
            'sku' => 'ATK-00002',
            'stock' => 50
        ]);
    }

    /** @test */
    public function ga_can_perform_stock_opname_adjustment()
    {
        $this->actingAs($this->ga);

        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 20,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        // Post adjustment
        $response = $this->post(route('atk.stock.adjust', $variant), [
            'physical_stock' => 17,
            'reason' => 'Stock opname, 3 rusak bocor'
        ]);

        $response->assertRedirect(route('atk.stock.index'));
        $this->assertDatabaseHas('atk_variants', [
            'id' => $variant->id,
            'stock' => 17
        ]);

        // Verify stock movement log
        $this->assertDatabaseHas('atk_stock_movements', [
            'variant_id' => $variant->id,
            'type' => 'adjustment',
            'previous_stock' => 20,
            'quantity' => -3,
            'new_stock' => 17,
            'actor_id' => $this->ga->id,
            'reason' => 'Stock opname, 3 rusak bocor'
        ]);
    }

    /** @test */
    public function normal_user_can_submit_request_and_cancel_while_submitted()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 20,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        $this->actingAs($this->user);

        // Browse catalog
        $response = $this->get(route('atk.catalog'));
        $response->assertStatus(200);

        // Submit request
        $response = $this->post(route('atk.requests.store'), [
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 5
                ]
            ],
            'note' => 'Untuk kebutuhan rapat divisi'
        ]);

        $response->assertRedirect(route('atk.requests.user'));
        $this->assertDatabaseHas('atk_requests', [
            'user_id' => $this->user->id,
            'status' => 'diajukan',
            'note' => 'Untuk kebutuhan rapat divisi'
        ]);

        $request = AtkRequest::first();

        // User can cancel request
        $response = $this->post(route('atk.requests.cancel', $request->id), [
            'cancellation_reason' => 'Salah quantity, mau bikin ulang'
        ]);

        $response->assertRedirect(route('atk.requests.user'));
        $this->assertDatabaseHas('atk_requests', [
            'id' => $request->id,
            'status' => 'dibatalkan',
            'cancellation_reason' => 'Salah quantity, mau bikin ulang'
        ]);
    }

    /** @test */
    public function ga_can_process_approved_request_which_deducts_stock_safely()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 20,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        $atkRequest = AtkRequest::create([
            'request_number' => 'REQ-ATK-20260821-0001',
            'user_id' => $this->user->id,
            'status' => 'diajukan',
        ]);

        $requestItem = AtkRequestItem::create([
            'request_id' => $atkRequest->id,
            'variant_id' => $variant->id,
            'quantity' => 15,
        ]);

        // GA approves
        $this->actingAs($this->ga);
        $response = $this->post(route('atk.requests.approve', $atkRequest->id));
        $response->assertRedirect(route('atk.requests.show', $atkRequest->id));
        $this->assertDatabaseHas('atk_requests', ['id' => $atkRequest->id, 'status' => 'disetujui']);

        // GA processes (Stock is deducted)
        $response = $this->post(route('atk.requests.process', $atkRequest->id));
        $response->assertRedirect(route('atk.requests.show', $atkRequest->id));
        
        $this->assertDatabaseHas('atk_requests', ['id' => $atkRequest->id, 'status' => 'diproses']);
        
        // Stock reduced from 20 to 5
        $this->assertDatabaseHas('atk_variants', ['id' => $variant->id, 'stock' => 5]);

        // Stock movement recorded
        $this->assertDatabaseHas('atk_stock_movements', [
            'variant_id' => $variant->id,
            'type' => 'request',
            'reference_id' => $requestItem->id,
            'previous_stock' => 20,
            'quantity' => -15,
            'new_stock' => 5
        ]);
    }

    /** @test */
    public function processing_request_fails_if_stock_insufficient()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 10, // Stock is only 10
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        $atkRequest = AtkRequest::create([
            'request_number' => 'REQ-ATK-20260821-0001',
            'user_id' => $this->user->id,
            'status' => 'disetujui', // Already approved
        ]);

        $requestItem = AtkRequestItem::create([
            'request_id' => $atkRequest->id,
            'variant_id' => $variant->id,
            'quantity' => 15, // Requests 15
        ]);

        $this->actingAs($this->ga);

        // Process request - should fail because stock is 10 but requested is 15
        $response = $this->post(route('atk.requests.process', $atkRequest->id));
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Assert request status is still 'disetujui' and stock remains 10
        $this->assertDatabaseHas('atk_requests', ['id' => $atkRequest->id, 'status' => 'disetujui']);
        $this->assertDatabaseHas('atk_variants', ['id' => $variant->id, 'stock' => 10]);
    }

    /** @test */
    public function unauthorized_users_are_blocked_from_atk_actions()
    {
        // 1. Trying to access catalog/requests without module access
        $this->actingAs($this->unauthorizedUser);

        $response = $this->get(route('atk.catalog'));
        $response->assertStatus(403); // Blocked by module middleware

        // 2. Normal user trying to access manager routes (dashboard)
        $this->actingAs($this->user); // Has 'atk' access, but not manager role
        $response = $this->get(route('atk.dashboard'));
        $response->assertStatus(403); // Blocked in controller
    }

    /** @test */
    public function submitting_request_fails_if_quantity_exceeds_stock()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 10,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        $this->actingAs($this->user);

        // Submit request with quantity 15 (exceeds stock 10)
        $response = $this->post(route('atk.requests.store'), [
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 15
                ]
            ],
            'note' => 'Coba minta berlebih'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertTrue(str_contains(session('error'), 'hanya tersisa 10 Pcs'));
    }

    /** @test */
    public function inactive_variant_cannot_be_requested()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 10,
            'minimum_stock' => 5,
            'is_active' => 0 // Inactive
        ]);

        $this->actingAs($this->user);

        // Try to submit request for inactive variant
        $response = $this->post(route('atk.requests.store'), [
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertTrue(str_contains(session('error'), 'sedang tidak aktif'));
    }

    /** @test */
    public function sku_is_generated_uniquely_with_retry()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);

        $this->actingAs($this->admin);

        // Create first variant
        $response1 = $this->post(route('atk.variants.store'), [
            'item_id' => $item->id,
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'purchase_price' => 10000,
            'stock' => 10,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);
        $response1->assertRedirect(route('atk.items.show', $item->id));
        $this->assertDatabaseHas('atk_variants', ['sku' => 'ATK-00001']);

        // Create second variant
        $response2 = $this->post(route('atk.variants.store'), [
            'item_id' => $item->id,
            'name' => 'Biru',
            'unit' => 'Pcs',
            'purchase_price' => 12000,
            'stock' => 10,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);
        $response2->assertRedirect(route('atk.items.show', $item->id));
        $this->assertDatabaseHas('atk_variants', ['sku' => 'ATK-00002']);
    }

    /** @test */
    public function admin_is_blocked_from_request_operations()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 20,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        $atkRequest = AtkRequest::create([
            'request_number' => 'REQ-ATK-20260821-0001',
            'user_id' => $this->user->id,
            'status' => 'diajukan',
        ]);

        $this->actingAs($this->admin);

        // 1. Try to approve
        $response = $this->post(route('atk.requests.approve', $atkRequest->id));
        $response->assertStatus(403);

        // 2. Try to reject
        $response = $this->post(route('atk.requests.reject', $atkRequest->id), [
            'rejection_reason' => 'Admin is not operator'
        ]);
        $response->assertStatus(403);

        // 3. Try to process
        $response = $this->post(route('atk.requests.process', $atkRequest->id));
        $response->assertStatus(403);

        // 4. Try to complete
        $response = $this->post(route('atk.requests.complete', $atkRequest->id));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_item_with_inline_category_and_variants()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('atk.items.store'), [
            'new_category_name' => 'Kertas Spesial',
            'new_category_description' => 'Kertas khusus cetak foto',
            'name' => 'Kertas Foto Glossy',
            'description' => 'Kertas glossy premium',
            'is_active' => 1,
            'variants' => [
                [
                    'name' => 'A4 200gr',
                    'unit' => 'Pack',
                    'purchase_price' => 25000,
                    'stock' => 20,
                    'minimum_stock' => 2,
                    'is_active' => 1,
                ],
                [
                    'name' => 'A3 200gr',
                    'unit' => 'Pack',
                    'purchase_price' => 45000,
                    'stock' => 10,
                    'minimum_stock' => 1,
                    'is_active' => 1,
                ]
            ]
        ]);

        $this->assertDatabaseHas('atk_categories', ['name' => 'Kertas Spesial']);
        $category = AtkCategory::where('name', 'Kertas Spesial')->first();

        $this->assertDatabaseHas('atk_items', [
            'name' => 'Kertas Foto Glossy',
            'category_id' => $category->id
        ]);
        $item = AtkItem::where('name', 'Kertas Foto Glossy')->first();

        $response->assertRedirect(route('atk.items.show', $item->id));

        $this->assertDatabaseHas('atk_variants', [
            'item_id' => $item->id,
            'name' => 'A4 200gr',
            'sku' => 'ATK-00001',
            'stock' => 20
        ]);
        $this->assertDatabaseHas('atk_variants', [
            'item_id' => $item->id,
            'name' => 'A3 200gr',
            'sku' => 'ATK-00002',
            'stock' => 10
        ]);
    }

    /** @test */
    public function variant_validation_rules_for_purchase_price()
    {
        $this->actingAs($this->admin);

        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);

        // 1. purchase_price is required
        $response = $this->post(route('atk.variants.store'), [
            'item_id' => $item->id,
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'stock' => 50,
            'minimum_stock' => 10,
            'is_active' => 1
        ]);
        $response->assertSessionHasErrors(['purchase_price']);

        // 2. purchase_price cannot be negative
        $response = $this->post(route('atk.variants.store'), [
            'item_id' => $item->id,
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'purchase_price' => -500,
            'stock' => 50,
            'minimum_stock' => 10,
            'is_active' => 1
        ]);
        $response->assertSessionHasErrors(['purchase_price']);

        // 3. purchase_price must be numeric
        $response = $this->post(route('atk.variants.store'), [
            'item_id' => $item->id,
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'purchase_price' => 'not-numeric',
            'stock' => 50,
            'minimum_stock' => 10,
            'is_active' => 1
        ]);
        $response->assertSessionHasErrors(['purchase_price']);
    }

    /** @test */
    public function request_item_captures_purchase_price_snapshot_and_calculates_subtotal()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'purchase_price' => 15000,
            'stock' => 20,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        $this->actingAs($this->user);

        // Submit request
        $response = $this->post(route('atk.requests.store'), [
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 3
                ]
            ],
            'note' => 'Test snapshot'
        ]);

        $response->assertRedirect(route('atk.requests.user'));

        $request = AtkRequest::first();
        $this->assertNotNull($request);

        $requestItem = AtkRequestItem::where('request_id', $request->id)->first();
        $this->assertNotNull($requestItem);

        // Assert snapshot values
        $this->assertEquals(15000.00, $requestItem->unit_price);
        $this->assertEquals(45000.00, $requestItem->subtotal);
    }

    /** @test */
    public function price_changes_do_not_affect_existing_snapshots()
    {
        $category = AtkCategory::create(['name' => 'Alat Tulis', 'is_active' => true]);
        $item = AtkItem::create(['category_id' => $category->id, 'name' => 'Pulpen', 'is_active' => true]);
        $variant = AtkVariant::create([
            'item_id' => $item->id,
            'sku' => 'ATK-00001',
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'purchase_price' => 15000,
            'stock' => 20,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        // Submit request under first price
        $this->actingAs($this->user);
        $this->post(route('atk.requests.store'), [
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $request = AtkRequest::first();
        $requestItem = AtkRequestItem::where('request_id', $request->id)->first();
        $this->assertEquals(15000.00, $requestItem->unit_price);
        $this->assertEquals(30000.00, $requestItem->subtotal);

        // Change the variant's purchase_price to 18000
        $this->actingAs($this->admin);
        $this->put(route('atk.variants.update', $variant), [
            'item_id' => $item->id,
            'name' => 'Hitam',
            'unit' => 'Pcs',
            'purchase_price' => 18000,
            'stock' => 20,
            'minimum_stock' => 5,
            'is_active' => 1
        ]);

        // Assert database variant price is updated
        $this->assertEquals(18000.00, $variant->fresh()->purchase_price);

        // Assert older request item snapshot remains 15000
        $this->assertEquals(15000.00, $requestItem->fresh()->unit_price);
        $this->assertEquals(30000.00, $requestItem->fresh()->subtotal);
    }
}
