<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\Contracts\UnitOfWork;
use Illuminate\Database\DatabaseManager;

final readonly class LaravelUnitOfWork implements UnitOfWork
{
    public function __construct(private DatabaseManager $database) {}

    public function transactional(callable $operation): mixed
    {
        return $this->database->transaction($operation);
    }
}
