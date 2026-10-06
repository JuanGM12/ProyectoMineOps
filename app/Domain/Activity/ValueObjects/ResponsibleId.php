<?php

declare(strict_types=1);

namespace App\Domain\Activity\ValueObjects;

use App\Domain\Activity\Exceptions\InvalidValueObject;

final readonly class ResponsibleId
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) !== 1) {
            throw InvalidValueObject::invalidUuid('responsible_id');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
