<?php

declare(strict_types=1);

namespace App\Domain\Activity\ValueObjects;

use App\Domain\Activity\Exceptions\InvalidValueObject;

final readonly class ActivityCode
{
    private const MAX_LENGTH = 50;

    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw InvalidValueObject::empty('code');
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw InvalidValueObject::tooLong('code', self::MAX_LENGTH);
        }

        $this->value = $value;
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
