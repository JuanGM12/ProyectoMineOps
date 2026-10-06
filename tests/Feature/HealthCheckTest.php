<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HealthCheckTest extends TestCase
{
    public function test_planning_service_reports_healthy_status(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'service' => 'planning-service',
                    'status' => 'ok',
                ],
            ]);
    }
}
