<?php

declare(strict_types=1);

namespace App\Application\Activity\Bus;

final readonly class MappedCommandBus implements CommandBus
{
    /**
     * @param  array<class-string<Command>, callable(Command): mixed>  $handlers
     */
    public function __construct(private array $handlers) {}

    public function dispatch(Command $command): mixed
    {
        $handler = $this->handlers[$command::class]
            ?? throw CommandHandlerNotFound::for($command);

        return $handler($command);
    }
}
