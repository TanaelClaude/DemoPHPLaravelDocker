<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    private string $viteHotFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viteHotFile = sys_get_temp_dir().'/laravel-vite-calendar-'.uniqid();
        file_put_contents($this->viteHotFile, 'http://localhost:5173');
        Vite::useHotFile($this->viteHotFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->viteHotFile);

        parent::tearDown();
    }

    public function test_calendar_requires_authentication(): void
    {
        $this->get('/calendar')->assertRedirect(route('login'));
        $this->post('/calendar/2026-03-02/validate')->assertRedirect(route('login'));
    }

    public function test_calendar_shows_the_week_from_monday_and_all_members(): void
    {
        CarbonImmutable::setTestNow('2026-03-04 12:00:00');
        $me = User::factory()->create(['name' => 'Current Member']);
        $other = User::factory()->create(['name' => 'Other Member']);
        $other->workSchedules()->create([
            'work_date' => '2026-03-03',
            'starts_at' => '08:30',
            'ends_at' => '16:30',
        ]);

        $response = $this->actingAs($me)->get('/calendar');

        $response->assertOk()
            ->assertSee('lundi')
            ->assertSee('02/03')
            ->assertSee('08:30 - 16:30')
            ->assertSee('Votre calendrier')
            ->assertSee('Other Member');

        CarbonImmutable::setTestNow();
    }

    public function test_week_submission_updates_only_the_authenticated_users_calendar(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $otherSchedule = $other->workSchedules()->create([
            'work_date' => '2026-03-02',
            'starts_at' => '10:00',
            'ends_at' => '18:00',
        ]);

        $this->actingAs($me)->post('/calendar/2026-03-02/validate', [
            'user_id' => $other->id,
            'schedule' => [
                '2026-03-02' => ['start' => '09:00', 'end' => '17:00'],
                '2026-03-03' => ['start' => '', 'end' => ''],
                '2026-03-04' => ['start' => '', 'end' => ''],
                '2026-03-05' => ['start' => '', 'end' => ''],
                '2026-03-06' => ['start' => '', 'end' => ''],
                '2026-03-07' => ['start' => '', 'end' => ''],
                '2026-03-08' => ['start' => '', 'end' => ''],
            ],
        ])->assertRedirect(route('calendar.index', ['week' => '2026-03-02']))
            ->assertSessionHas('status', 'Votre semaine a été validée.');

        $this->assertDatabaseHas('work_schedules', [
            'user_id' => $me->id,
            'work_date' => '2026-03-02',
            'starts_at' => '09:00',
            'ends_at' => '17:00',
        ]);
        $this->assertDatabaseHas('work_schedules', [
            'id' => $otherSchedule->id,
            'user_id' => $other->id,
            'starts_at' => '10:00',
            'ends_at' => '18:00',
        ]);
        $this->assertDatabaseHas('week_validations', [
            'user_id' => $me->id,
            'week_start' => '2026-03-02',
        ]);
        $this->assertDatabaseMissing('week_validations', [
            'user_id' => $other->id,
            'week_start' => '2026-03-02',
        ]);
    }

    public function test_week_submission_rejects_non_monday_and_invalid_hours(): void
    {
        $me = User::factory()->create();
        $week = [
            '2026-03-02' => ['start' => '17:00', 'end' => '09:00'],
            '2026-03-03' => ['start' => '', 'end' => ''],
            '2026-03-04' => ['start' => '', 'end' => ''],
            '2026-03-05' => ['start' => '', 'end' => ''],
            '2026-03-06' => ['start' => '', 'end' => ''],
            '2026-03-07' => ['start' => '', 'end' => ''],
            '2026-03-08' => ['start' => '', 'end' => ''],
        ];

        $this->actingAs($me)->post('/calendar/2026-03-02/validate', ['schedule' => $week])
            ->assertSessionHasErrors('schedule.2026-03-02.end');
        $this->assertDatabaseCount('week_validations', 0);
        $this->assertDatabaseCount('work_schedules', 0);

        $this->post('/calendar/2026-03-03/validate', ['schedule' => $week])
            ->assertSessionHasErrors('schedule');
        $this->assertDatabaseCount('week_validations', 0);
    }

    public function test_week_submission_rejects_dates_outside_the_requested_week(): void
    {
        $me = User::factory()->create();
        $schedule = array_fill_keys(
            ['2026-03-02', '2026-03-03', '2026-03-04', '2026-03-05', '2026-03-06', '2026-03-07', '2026-03-08'],
            ['start' => '', 'end' => ''],
        );
        $schedule['2026-03-09'] = ['start' => '09:00', 'end' => '17:00'];

        $this->actingAs($me)->post('/calendar/2026-03-02/validate', ['schedule' => $schedule])
            ->assertSessionHasErrors('schedule');

        $this->assertDatabaseCount('work_schedules', 0);
        $this->assertDatabaseCount('week_validations', 0);
    }
}
