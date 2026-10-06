<?php

declare(strict_types=1);

namespace App\Domain\Activity\Exceptions;

final class InvalidValueObject extends DomainException
{
    public static function empty(string $field): self
    {
        return new self("El campo {$field} no puede estar vacío.");
    }

    public static function tooLong(string $field, int $maximum): self
    {
        return new self("El campo {$field} no puede superar {$maximum} caracteres.");
    }

    public static function invalidUuid(string $field): self
    {
        return new self("El campo {$field} debe contener un UUID válido.");
    }
}
