<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Activity;

use App\Domain\Activity\Exceptions\InvalidValueObject;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_activity_id_can_be_generated_as_uuid(): void
    {
        $id = ActivityId::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $id->value(),
        );
    }

    public function test_activity_code_is_trimmed(): void
    {
        self::assertSame('ACT-001', (new ActivityCode('  ACT-001  '))->value());
    }

    public function test_invalid_responsible_id_is_rejected(): void
    {
        $this->expectException(InvalidValueObject::class);

        new ResponsibleId('not-a-uuid');
    }
}
