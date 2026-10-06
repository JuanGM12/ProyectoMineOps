<?php

declare(strict_types=1);

namespace App\Application\Activity\Bus;

final readonly class MappedQueryBus implements QueryBus
{
    /**
     * @param  array<class-string<Query>, callable(Query): mixed>  $handlers
     */
    public function __construct(private array $handlers) {}

    public function ask(Query $query): mixed
    {
        $handler = $this->handlers[$query::class]
            ?? throw QueryHandlerNotFound::for($query);

        return $handler($query);
    }
}
