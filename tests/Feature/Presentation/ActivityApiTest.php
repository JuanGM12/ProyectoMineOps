<?php

declare(strict_types=1);

namespace Tests\Feature\Presentation;

use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

final class ActivityApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_payload_creates_activity_and_returns_201(): void
    {
        $response = $this->postJson('/api/v1/activities', $this->payload());

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'ACT-001')
            ->assertJsonPath('data.status', 'PENDING');
        $this->assertDatabaseHas('activities', [
            'code' => 'ACT-001',
            'title' => 'Inspección del frente norte',
            'status' => 'PENDING',
        ]);
    }

    public function test_index_returns_filtered_paginated_activities(): void
    {
        $this->persistActivity('00000000-0000-4000-8000-000000000001', 'ACT-001', ActivityPriority::HIGH);
        $this->persistActivity('00000000-0000-4000-8000-000000000002', 'ACT-002', ActivityPriority::LOW);

        $response = $this->getJson('/api/v1/activities?priority=HIGH&page=1&per_page=1');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'ACT-001')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_activity_can_be_shown_and_updated(): void
    {
        $id = '00000000-0000-4000-8000-000000000001';
        $this->persistActivity($id, 'ACT-001');

        $this->getJson("/api/v1/activities/{$id}")
            ->assertOk()
            ->assertJsonPath('data.code', 'ACT-001');

        $response = $this->putJson("/api/v1/activities/{$id}", $this->payload([
            'code' => null,
            'title' => 'Actividad actualizada',
            'priority' => 'CRITICAL',
            'scheduled_date' => '2026-10-12',
            'due_date' => '2026-10-14',
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Actividad actualizada')
            ->assertJsonPath('data.priority', 'CRITICAL')
            ->assertJsonPath('data.due_date', '2026-10-14');
        $this->assertDatabaseHas('activities', [
            'id' => $id,
            'code' => 'ACT-001',
            'title' => 'Actividad actualizada',
        ]);
    }

    public function test_activity_state_endpoints_dispatch_domain_behaviour(): void
    {
        $startedId = '00000000-0000-4000-8000-000000000001';
        $cancelledId = '00000000-0000-4000-8000-000000000002';
        $this->persistActivity($startedId, 'ACT-001');
        $this->persistActivity($cancelledId, 'ACT-002');

        $this->patchJson("/api/v1/activities/{$startedId}/start")
            ->assertOk()
            ->assertJsonPath('data.status', 'IN_PROGRESS');
        $this->patchJson("/api/v1/activities/{$startedId}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'COMPLETED');
        $this->patchJson("/api/v1/activities/{$cancelledId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'CANCELLED');
    }

    public function test_missing_activity_returns_consistent_404(): void
    {
        $this->getJson('/api/v1/activities/00000000-0000-4000-8000-000000000099')
            ->assertNotFound()
            ->assertExactJson([
                'success' => false,
                'message' => 'No existe una actividad con id 00000000-0000-4000-8000-000000000099.',
                'errors' => [],
            ]);
    }

    public function test_invalid_http_and_domain_data_return_422(): void
    {
        $this->postJson('/api/v1/activities', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['code', 'title', 'area', 'location', 'responsible_id', 'priority', 'scheduled_date']);

        $this->postJson('/api/v1/activities', $this->payload([
            'priority' => 'CRITICAL',
            'due_date' => null,
        ]))
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors', []);

        $this->postJson('/api/v1/activities', $this->payload([
            'due_date' => '2026-10-09',
        ]))
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_duplicates_and_invalid_state_transitions_return_409(): void
    {
        $this->postJson('/api/v1/activities', $this->payload())->assertCreated();
        $this->postJson('/api/v1/activities', $this->payload())
            ->assertConflict()
            ->assertJsonPath('success', false);

        $id = (string) $this->getJson('/api/v1/activities')->json('data.0.id');
        $this->patchJson("/api/v1/activities/{$id}/complete")
            ->assertConflict()
            ->assertJsonPath('success', false);
        $this->patchJson("/api/v1/activities/{$id}/start")->assertOk();
        $this->patchJson("/api/v1/activities/{$id}/complete")->assertOk();
        $this->patchJson("/api/v1/activities/{$id}/start")
            ->assertConflict()
            ->assertJsonPath('success', false);
    }

    public function test_pending_overdue_and_responsible_endpoints_apply_their_filters(): void
    {
        $this->travelTo('2026-10-20 08:00:00');
        $responsibleId = 'd7794c47-cb29-4f1b-ab36-e5f7568a52fe';
        $this->persistActivity(
            '00000000-0000-4000-8000-000000000001',
            'ACT-001',
            responsibleId: $responsibleId,
            dueDate: '2026-10-19',
        );
        $this->persistActivity(
            '00000000-0000-4000-8000-000000000002',
            'ACT-002',
            responsibleId: 'eb5a466f-f69d-4b69-a727-5dcb52fe9e39',
            dueDate: '2026-10-21',
        );

        $this->getJson('/api/v1/activities/pending')
            ->assertOk()
            ->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/activities/overdue')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'ACT-001');
        $this->getJson("/api/v1/activities/responsible/{$responsibleId}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'ACT-001');
    }

    /** @param array<string, mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        $payload = [
            'code' => 'ACT-001',
            'title' => 'Inspección del frente norte',
            'description' => 'Validar las condiciones del área.',
            'area' => 'Operaciones',
            'location' => 'Frente norte',
            'responsible_id' => 'd7794c47-cb29-4f1b-ab36-e5f7568a52fe',
            'priority' => 'MEDIUM',
            'scheduled_date' => '2026-10-10',
            'due_date' => '2026-10-11',
        ];

        return array_filter(
            array_replace($payload, $overrides),
            static fn (mixed $value, string $key): bool => ! ($key === 'code' && $value === null),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private function persistActivity(
        string $id,
        string $code,
        ActivityPriority $priority = ActivityPriority::MEDIUM,
        string $responsibleId = 'd7794c47-cb29-4f1b-ab36-e5f7568a52fe',
        string $dueDate = '2026-10-11',
    ): void {
        $activity = Activity::create(
            new ActivityId($id),
            new ActivityCode($code),
            new ActivityTitle("Actividad {$code}"),
            'Descripción de prueba',
            'Operaciones',
            'Frente norte',
            new ResponsibleId($responsibleId),
            $priority,
            new ActivitySchedule(
                new DateTimeImmutable('2026-10-10'),
                new DateTimeImmutable($dueDate),
            ),
        );

        $this->app->make(ActivityRepository::class)->save($activity);
    }
}
