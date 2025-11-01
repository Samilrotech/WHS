<?php

namespace Tests\Feature;

use App\Features\DenseTableFeature;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Feature;
use Tests\TestCase;

class DenseTableFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure feature is enabled in config (not emergency disabled)
        Config::set('app.dense_table_enabled', true);
    }

    /** @test */
    public function it_enables_dense_table_for_sydney_operations_centre_users()
    {
        $sydneyBranch = Branch::factory()->create([
            'id' => '019a2a03-5c3e-72ce-90f4-423356c32441',
            'name' => 'Sydney Operations Centre',
        ]);

        $user = User::factory()->create(['branch_id' => $sydneyBranch->id]);

        // Phase 1: Sydney users should have feature enabled
        $this->assertTrue(Feature::for($user)->active('dense-table'));
    }

    /** @test */
    public function it_disables_dense_table_for_non_sydney_users_in_phase_1()
    {
        $brisbane = Branch::factory()->create([
            'id' => '019a2a03-5c41-72d8-b2ed-baca867cbb2a',
            'name' => 'Brisbane Logistics Hub',
        ]);

        $user = User::factory()->create(['branch_id' => $brisbane->id]);

        // Before Phase 2 date: Non-Sydney users should NOT have feature enabled
        Carbon::setTestNow('2025-11-05'); // Week 1

        $this->assertFalse(Feature::for($user)->active('dense-table'));
    }

    /** @test */
    public function it_enables_dense_table_for_50_percent_after_phase_2_date()
    {
        $brisbane = Branch::factory()->create([
            'id' => '019a2a03-5c41-72d8-b2ed-baca867cbb2a',
            'name' => 'Brisbane Logistics Hub',
        ]);

        // Phase 2: 50% A/B test (Nov 8-21)
        Carbon::setTestNow('2025-11-10');

        $results = [];
        for ($i = 0; $i < 100; $i++) {
            $user = User::factory()->create(['branch_id' => $brisbane->id]);
            $results[] = Feature::for($user)->active('dense-table');
        }

        $enabledCount = count(array_filter($results));
        $enabledPercentage = ($enabledCount / count($results)) * 100;

        // Should be approximately 50% (allowing for randomness ±20%)
        $this->assertGreaterThanOrEqual(30, $enabledPercentage);
        $this->assertLessThanOrEqual(70, $enabledPercentage);
    }

    /** @test */
    public function it_enables_dense_table_for_all_users_after_phase_3_date()
    {
        $perth = Branch::factory()->create([
            'id' => '019a2a03-5c44-714f-a26f-0ff96b6e6ead',
            'name' => 'Perth Regional Office',
        ]);

        $user = User::factory()->create(['branch_id' => $perth->id]);

        // Phase 3: 100% rollout (Nov 22+)
        Carbon::setTestNow('2025-11-25');

        $this->assertTrue(Feature::for($user)->active('dense-table'));
    }

    /** @test */
    public function it_respects_emergency_disable_flag()
    {
        $sydneyBranch = Branch::factory()->create([
            'id' => '019a2a03-5c3e-72ce-90f4-423356c32441',
            'name' => 'Sydney Operations Centre',
        ]);

        $user = User::factory()->create(['branch_id' => $sydneyBranch->id]);

        // Emergency disable
        Config::set('app.dense_table_enabled', false);

        // Even Sydney users (Phase 1) should have feature disabled
        $this->assertFalse(Feature::for($user)->active('dense-table'));
    }

    /** @test */
    public function it_passes_feature_flag_to_team_index_view()
    {
        $sydney = Branch::factory()->create([
            'id' => '019a2a03-5c3e-72ce-90f4-423356c32441',
            'name' => 'Sydney Operations Centre',
        ]);

        $admin = User::factory()->admin()->create(['branch_id' => $sydney->id]);

        $response = $this->actingAs($admin)->get(route('teams.index'));

        $response->assertStatus(200);
        $response->assertViewHas('useDenseTable', true);
    }

    /** @test */
    public function it_shows_dense_table_notice_when_feature_active()
    {
        $sydney = Branch::factory()->create([
            'id' => '019a2a03-5c3e-72ce-90f4-423356c32441',
            'name' => 'Sydney Operations Centre',
        ]);

        $admin = User::factory()->admin()->create(['branch_id' => $sydney->id]);

        $response = $this->actingAs($admin)->get(route('teams.index'));

        $response->assertStatus(200);
        $response->assertSee('New Dense Table UI Active');
        $response->assertSee('high-density table interface');
    }

    /** @test */
    public function it_hides_dense_table_notice_when_feature_inactive()
    {
        $brisbane = Branch::factory()->create([
            'id' => '019a2a03-5c41-72d8-b2ed-baca867cbb2a',
            'name' => 'Brisbane Logistics Hub',
        ]);

        $admin = User::factory()->admin()->create(['branch_id' => $brisbane->id]);

        // Phase 1: Before Nov 8, non-Sydney users should NOT see notice
        Carbon::setTestNow('2025-11-05');

        $response = $this->actingAs($admin)->get(route('teams.index'));

        $response->assertStatus(200);
        $response->assertDontSee('New Dense Table UI Active');
    }
}
