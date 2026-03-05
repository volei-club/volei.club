<?php

namespace Tests\Feature\Api;

use App\Models\Club;
use App\Models\Location;
use App\Models\Squad;
use App\Models\Team;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected $club;
    protected $athlete;
    protected $squad;
    protected $location;
    protected $team;
    protected $coach;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['name' => 'Test Club']);
        $this->coach = User::factory()->create(['role' => 'antrenor', 'club_id' => $this->club->id]);
        $this->athlete = User::factory()->create(['role' => 'sportiv', 'club_id' => $this->club->id]);

        $this->location = Location::create([
            'club_id' => $this->club->id,
            'name' => 'Test Hall',
            'address' => 'Test St 1',
        ]);

        $this->team = Team::create(['club_id' => $this->club->id, 'name' => 'Test Team']);

        $this->squad = Squad::create([
            'team_id' => $this->team->id,
            'name' => 'Test Squad',
            'created_by' => User::factory()->create(['role' => 'administrator'])->id,
        ]);
        $this->squad->users()->attach($this->athlete->id);
    }

    public function test_calendar_respects_training_date_bounds(): void
    {
        // Friday, March 6, 2026
        $targetFriday = Carbon::parse('2026-03-06');

        // Training only on Fridays
        $training = Training::create([
            'club_id' => $this->club->id,
            'location_id' => $this->location->id,
            'team_id' => $this->team->id,
            'squad_id' => $this->squad->id,
            'coach_id' => $this->coach->id,
            'day_of_week' => 'vineri',
            'start_time' => '10:00',
            'end_time' => '12:00',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-10', // Should only appear on March 6
        ]);

        // Scenario 1: Request calendar including March 6
        $response = $this->actingAs($this->athlete, 'sanctum')
            ->getJson('/api/my-calendar?start_date=2026-03-01&weeks=2');

        $response->assertStatus(200);
        $data = $response->json('data');

        $dates = collect($data)->pluck('date')->toArray();
        $this->assertContains('2026-03-06', $dates);
        // Next friday is March 13, which is outside end_date
        $this->assertNotContains('2026-03-13', $dates);
    }

    public function test_calendar_excludes_cancelled_training_instances(): void
    {
        // Monday, March 9, 2026
        $targetMonday = '2026-03-09';

        $training = Training::create([
            'club_id' => $this->club->id,
            'location_id' => $this->location->id,
            'team_id' => $this->team->id,
            'squad_id' => $this->squad->id,
            'coach_id' => $this->coach->id,
            'day_of_week' => 'luni',
            'start_time' => '10:00',
            'end_time' => '12:00',
        ]);

        // Cancel the instance of March 9
        $training->cancellations()->create([
            'date' => $targetMonday,
            'reason' => 'Holiday',
        ]);

        $response = $this->actingAs($this->athlete, 'sanctum')
            ->getJson('/api/my-calendar?start_date=2026-03-01&weeks=4');

        $response->assertStatus(200);
        $data = $response->json('data');

        $dates = collect($data)->where('training_id', $training->id)->pluck('date')->toArray();

        // March 2 should be there, March 9 should NOT
        $this->assertContains('2026-03-02', $dates);
        $this->assertNotContains('2026-03-09', $dates);
    }
}
