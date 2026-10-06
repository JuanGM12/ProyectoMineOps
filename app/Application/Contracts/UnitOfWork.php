<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface UnitOfWork
{
    /**
     * @template TResult
     *
     * @param  callable(): TResult  $operation
     * @return TResult
     */
    public function transactional(callable $operation): mixed;
}
