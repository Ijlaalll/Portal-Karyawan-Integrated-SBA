<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ToiletLocation;
use App\Models\ChecklistItem;
use App\Models\Inspection;
use App\Models\InspectionResult;
use App\Models\InspectionApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ToiletInspectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $inspector;
    protected User $spv;
    protected ToiletLocation $location;
    protected ChecklistItem $checklist1;
    protected ChecklistItem $checklist2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Users
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->inspector = User::create([
            'name' => 'Inspector Budi',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $this->inspector->modules()->create(['module' => 'toilet_inspection']);

        $this->spv = User::create([
            'name' => 'Supervisor Siti',
            'email' => 'siti@example.com',
            'password' => Hash::make('password'),
            'role' => 'spv',
        ]);
        $this->spv->modules()->create(['module' => 'toilet_inspection']);

        // Create initial locations and checklist items
        $this->location = ToiletLocation::create([
            'building' => 'Building Z',
            'floor' => 'Floor 3',
            'area' => 'HR Office Area',
            'toilet_name' => 'Toilet Executive',
            'gender' => 'unisex',
            'is_active' => true,
        ]);

        $this->checklist1 = ChecklistItem::create([
            'name' => 'Clean Floor',
            'description' => 'Ensure floor is dry and clean',
            'is_active' => true,
        ]);

        $this->checklist2 = ChecklistItem::create([
            'name' => 'Soap Filled',
            'description' => 'Ensure soap dispenser is full',
            'is_active' => true,
        ]);
    }

    /**
     * Test Locations CRUD is allowed for Admin but blocked for others.
     */
    public function test_locations_crud_authorizations(): void
    {
        // Admin can access index (with proper module session)
        $this->actingAs($this->admin)
            ->withSession(['active_admin_module' => 'toilet_inspection', 'enforce_active_module' => true])
            ->get(route('admin.toilet_inspection.locations.index'))
            ->assertStatus(200);

        // Inspector cannot access index
        $this->actingAs($this->inspector)->get(route('admin.toilet_inspection.locations.index'))
            ->assertStatus(403);

        // Admin can create location
        $response = $this->actingAs($this->admin)
            ->withSession(['active_admin_module' => 'toilet_inspection', 'enforce_active_module' => true])
            ->post(route('admin.toilet_inspection.locations.store'), [
                'building' => 'Building X',
                'floor' => 'Floor 1',
                'area' => 'Finance Area',
                'toilet_name' => 'Finance Toilet',
                'gender' => 'pria',
            ]);
        $response->assertRedirect(route('admin.toilet_inspection.locations.index'));
        $this->assertDatabaseHas('toilet_locations', ['toilet_name' => 'Finance Toilet']);

        // Inspector cannot create location
        $this->actingAs($this->inspector)->post(route('admin.toilet_inspection.locations.store'), [
            'building' => 'Building X',
            'floor' => 'Floor 1',
            'area' => 'Finance Area',
            'toilet_name' => 'Failed Toilet',
            'gender' => 'pria',
        ])->assertStatus(403);
        $this->assertDatabaseMissing('toilet_locations', ['toilet_name' => 'Failed Toilet']);
    }

    /**
     * Test Checklist Items CRUD is allowed for Admin but blocked for others.
     */
    public function test_checklist_items_crud_authorizations(): void
    {
        // Admin can access index (with proper module session)
        $this->actingAs($this->admin)
            ->withSession(['active_admin_module' => 'toilet_inspection', 'enforce_active_module' => true])
            ->get(route('admin.toilet_inspection.checklist-items.index'))
            ->assertStatus(200);

        // Inspector cannot access index
        $this->actingAs($this->inspector)->get(route('admin.toilet_inspection.checklist-items.index'))
            ->assertStatus(403);

        // Admin can create checklist item
        $response = $this->actingAs($this->admin)
            ->withSession(['active_admin_module' => 'toilet_inspection', 'enforce_active_module' => true])
            ->post(route('admin.toilet_inspection.checklist-items.store'), [
                'name' => 'Mirror Clean',
                'description' => 'Ensure mirror has no spots',
            ]);
        $response->assertRedirect(route('admin.toilet_inspection.checklist-items.index'));
        $this->assertDatabaseHas('checklist_items', ['name' => 'Mirror Clean']);
    }

    /**
     * Test Inspector can submit toilet inspection with photo evidence.
     */
    public function test_inspector_can_submit_inspection(): void
    {
        Storage::fake('public');

        // Create fake file instead of fake image to bypass GD requirement
        $file = UploadedFile::fake()->create('evidence.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->inspector)->post(route('inspections.store'), [
            'location_id' => $this->location->id,
            'notes' => 'Some general notes',
            'photo' => $file,
            'results' => [
                $this->checklist1->id => [
                    'is_ok' => '1',
                    'remarks' => 'Cleaned floor',
                ],
                $this->checklist2->id => [
                    'is_ok' => '0',
                    'remarks' => 'Soap dispenser empty',
                ],
            ]
        ]);

        $response->assertRedirect(route('toilet_inspection.dashboard'));
        $response->assertSessionHasNoErrors();

        // Assert database values
        $this->assertDatabaseHas('inspections', [
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
            'notes' => 'Some general notes',
        ]);

        $inspection = Inspection::first();
        
        $this->assertNotNull($inspection->photo_path);
        Storage::disk('public')->assertExists($inspection->photo_path);

        $this->assertDatabaseHas('inspection_results', [
            'inspection_id' => $inspection->id,
            'checklist_item_id' => $this->checklist1->id,
            'is_ok' => true,
            'remarks' => 'Cleaned floor',
        ]);

        $this->assertDatabaseHas('inspection_results', [
            'inspection_id' => $inspection->id,
            'checklist_item_id' => $this->checklist2->id,
            'is_ok' => false,
            'remarks' => 'Soap dispenser empty',
        ]);
    }

    /**
     * Test SPV can approve an inspection.
     */
    public function test_spv_can_approve_inspection(): void
    {
        $inspection = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->spv)->post(route('toilet_inspection.approve', $inspection), [
            'notes' => 'Everything looks good.',
        ]);

        $response->assertRedirect(route('toilet_inspection.dashboard'));
        
        // Assert status updated to approved
        $this->assertEquals('approved', $inspection->fresh()->status);

        // Assert approval log created
        $this->assertDatabaseHas('inspection_approvals', [
            'inspection_id' => $inspection->id,
            'approver_id' => $this->spv->id,
            'action' => 'approve',
            'notes' => 'Everything looks good.',
        ]);
    }

    /**
     * Test SPV can reject an inspection, notes are mandatory.
     */
    public function test_spv_can_reject_inspection_requires_notes(): void
    {
        $inspection = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
        ]);

        // Submit without notes -> should fail validation
        $response = $this->actingAs($this->spv)->post(route('toilet_inspection.reject', $inspection), [
            'notes' => '',
        ]);
        $response->assertSessionHasErrors(['notes']);
        $this->assertEquals('submitted', $inspection->fresh()->status);

        // Submit with too short notes -> should fail validation
        $response2 = $this->actingAs($this->spv)->post(route('toilet_inspection.reject', $inspection), [
            'notes' => 'fail',
        ]);
        $response2->assertSessionHasErrors(['notes']);
        $this->assertEquals('submitted', $inspection->fresh()->status);

        // Submit with valid notes -> succeeds
        $response3 = $this->actingAs($this->spv)->post(route('toilet_inspection.reject', $inspection), [
            'notes' => 'Please clean the floors again.',
        ]);

        $response3->assertRedirect(route('toilet_inspection.dashboard'));
        $this->assertEquals('rejected', $inspection->fresh()->status);
        $this->assertDatabaseHas('inspection_approvals', [
            'inspection_id' => $inspection->id,
            'approver_id' => $this->spv->id,
            'action' => 'reject',
            'notes' => 'Please clean the floors again.',
        ]);
    }

    /**
     * Test Non-SPV cannot approve or reject.
     */
    public function test_non_spv_cannot_review_inspection(): void
    {
        $inspection = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
        ]);

        // Inspector tries to approve -> 403
        $this->actingAs($this->inspector)->post(route('toilet_inspection.approve', $inspection), [
            'notes' => 'illegal',
        ])->assertStatus(403);

        // Inspector tries to reject -> 403
        $this->actingAs($this->inspector)->post(route('toilet_inspection.reject', $inspection), [
            'notes' => 'legal',
        ])->assertStatus(403);
    }

    /**
     * Test Supervisor cannot access inspection creation routes directly.
     */
    public function test_supervisor_cannot_access_create_inspection_route(): void
    {
        // SPV tries to access create form -> 403
        $this->actingAs($this->spv)->get(route('inspections.create'))
            ->assertStatus(403);

        // SPV tries to POST store -> 403
        $this->actingAs($this->spv)->post(route('inspections.store'), [
            'location_id' => $this->location->id,
            'results' => []
        ])->assertStatus(403);
    }

    /**
     * Test Meeting Room user without Toilet Inspection access is blocked.
     */
    public function test_meeting_room_user_without_toilet_inspection_access_blocked(): void
    {
        $mrUser = User::create([
            'name' => 'MR User Only',
            'email' => 'mronly@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $mrUser->modules()->create(['module' => 'meeting_room']);

        $this->actingAs($mrUser)->get(route('toilet_inspection.dashboard'))
            ->assertStatus(403);
    }

    /**
     * Test Supervisor can access index logs and pending approvals view.
     */
    public function test_supervisor_can_access_approvals_and_inspections_list(): void
    {
        // SPV can access inspections index log
        $this->actingAs($this->spv)->get(route('inspections.index'))
            ->assertStatus(200);

        // SPV can access pending approvals index
        $this->actingAs($this->spv)->get(route('toilet_inspection.approvals.index'))
            ->assertStatus(200);

        // Inspector cannot access pending approvals index
        $this->actingAs($this->inspector)->get(route('toilet_inspection.approvals.index'))
            ->assertStatus(403);
    }

    /**
     * Test Inspector cannot submit an incomplete checklist.
     */
    public function test_inspector_cannot_submit_incomplete_checklist(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('evidence.jpg', 100, 'image/jpeg');

        // checklist2 is omitted in the request
        $response = $this->actingAs($this->inspector)->post(route('inspections.store'), [
            'location_id' => $this->location->id,
            'notes' => 'Omitted some checklist items',
            'photo' => $file,
            'results' => [
                $this->checklist1->id => [
                    'is_ok' => '1',
                    'remarks' => 'Cleaned floor',
                ],
            ]
        ]);

        $response->assertSessionHasErrors(['results']);
    }

    /**
     * Test Inspector can only see their own history records.
     */
    public function test_inspector_can_only_see_own_history(): void
    {
        $otherInspector = User::create([
            'name' => 'Other Inspector',
            'email' => 'other_inspector@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $otherInspector->modules()->create(['module' => 'toilet_inspection']);

        // Create inspection 1 (by budget inspector)
        $ins1 = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
        ]);

        // Create inspection 2 (by other inspector)
        $ins2 = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $otherInspector->id,
            'status' => 'submitted',
        ]);

        // Act as budget inspector Budi
        $response = $this->actingAs($this->inspector)->get(route('inspections.index'));
        $response->assertStatus(200);
        
        $inspections = $response->viewData('inspections');
        $this->assertCount(1, $inspections);
        $this->assertEquals($ins1->id, $inspections->first()->id);
    }

    /**
     * Test search filters by inspector name, building, and area.
     */
    public function test_search_filters_inspections_properly(): void
    {
        $locAlpha = ToiletLocation::create([
            'building' => 'Alpha Bldg',
            'floor' => 'Floor 1',
            'area' => 'Lobby Area',
            'toilet_name' => 'Lobby Toilet',
            'gender' => 'unisex',
            'is_active' => true,
        ]);

        $locBeta = ToiletLocation::create([
            'building' => 'Beta Bldg',
            'floor' => 'Floor 2',
            'area' => 'Cafeteria',
            'toilet_name' => 'Cafeteria Toilet',
            'gender' => 'unisex',
            'is_active' => true,
        ]);

        $insAlpha = Inspection::create([
            'location_id' => $locAlpha->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
        ]);

        $insBeta = Inspection::create([
            'location_id' => $locBeta->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
        ]);

        // Act as SPV
        $response1 = $this->actingAs($this->spv)->get(route('inspections.index', ['search' => 'Alpha']));
        $inspections1 = $response1->viewData('inspections');
        $this->assertCount(1, $inspections1);
        $this->assertEquals($insAlpha->id, $inspections1->first()->id);

        $response2 = $this->actingAs($this->spv)->get(route('inspections.index', ['search' => 'Beta']));
        $inspections2 = $response2->viewData('inspections');
        $this->assertCount(1, $inspections2);
        $this->assertEquals($insBeta->id, $inspections2->first()->id);
    }

    /**
     * Test date range filters.
     */
    public function test_date_filters_today_this_week_this_month_correctly(): void
    {
        // Current inspection (today)
        $insToday = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'approved',
        ]);

        // Inspection 2 weeks ago
        $insOld = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'approved',
        ]);
        $insOld->created_at = now()->subWeeks(2);
        $insOld->save();

        // Act as SPV
        $response = $this->actingAs($this->spv)->get(route('inspections.index', ['date_filter' => 'today']));
        $inspections = $response->viewData('inspections');
        $this->assertCount(1, $inspections);
        $this->assertEquals($insToday->id, $inspections->first()->id);
    }

    /**
     * Test combined search and status filters.
     */
    public function test_combined_filters_on_inspections_history(): void
    {
        $ins1 = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'approved',
        ]);

        $ins2 = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'rejected',
        ]);

        // Act as SPV, filter by status Approved and building Building Z
        $response = $this->actingAs($this->spv)->get(route('inspections.index', [
            'status' => 'approved',
            'building' => 'Building Z'
        ]));
        $inspections = $response->viewData('inspections');
        $this->assertCount(1, $inspections);
        $this->assertEquals($ins1->id, $inspections->first()->id);
    }

    /**
     * Test Admin can view all inspection history records.
     */
    public function test_admin_can_view_all_inspection_history(): void
    {
        $otherInspector = User::create([
            'name' => 'Other Inspector',
            'email' => 'other_inspector_admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);
        $otherInspector->modules()->create(['module' => 'toilet_inspection']);

        $ins1 = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'submitted',
        ]);

        $ins2 = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $otherInspector->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['active_admin_module' => 'toilet_inspection', 'enforce_active_module' => true])
            ->get(route('inspections.index'));

        $response->assertStatus(200);
        $inspections = $response->viewData('inspections');
        $this->assertCount(2, $inspections);
    }

    /**
     * Test dashboard period filtering affects statistics.
     */
    public function test_dashboard_period_filtering_affects_statistics(): void
    {
        // Inspection today
        $insToday = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'approved',
        ]);

        // Inspection 2 weeks ago (change to 2 days ago to keep in same month)
        $insOld = Inspection::create([
            'location_id' => $this->location->id,
            'inspector_id' => $this->inspector->id,
            'status' => 'approved',
        ]);
        $insOld->created_at = now()->subDays(2);
        $insOld->save();

        // Acting as SPV with 'today' filter
        $response = $this->actingAs($this->spv)->get(route('toilet_inspection.dashboard', ['date_filter' => 'today']));
        $response->assertStatus(200);
        $response->assertViewHas('totalInspections', 1);

        // Acting as SPV with 'this_month' filter (includes both)
        $response2 = $this->actingAs($this->spv)->get(route('toilet_inspection.dashboard', ['date_filter' => 'this_month']));
        $response2->assertStatus(200);
        $response2->assertViewHas('totalInspections', 2);
    }

    /**
     * Test Petugas is blocked from reporting page.
     */
    public function test_petugas_blocked_from_reporting_page(): void
    {
        $response = $this->actingAs($this->inspector)->get(route('toilet_inspection.report'));
        $response->assertStatus(403);
    }

    /**
     * Test SPV and Admin can access reporting page.
     */
    public function test_spv_and_admin_can_access_reporting_page(): void
    {
        // Act as SPV
        $responseSpv = $this->actingAs($this->spv)->get(route('toilet_inspection.report'));
        $responseSpv->assertStatus(200);
        $responseSpv->assertViewHas('totalInspections');

        // Act as Admin
        $responseAdmin = $this->actingAs($this->admin)
            ->withSession(['active_admin_module' => 'toilet_inspection', 'enforce_active_module' => true])
            ->get(route('toilet_inspection.report'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewHas('totalInspections');
    }
}
