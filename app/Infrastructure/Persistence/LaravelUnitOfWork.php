<?php
declare(strict_types=1);
namespace App\Infrastructure\Persistence;
use App\Application\Contracts\UnitOfWork;
use Illuminate\Support\Facades\DB;
final class LaravelUnitOfWork implements UnitOfWork
{
    public function transactional(callable $operation): mixed { return DB::transaction($operation); }
}
