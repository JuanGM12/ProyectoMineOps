<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Activity\Bus;

use App\Application\Activity\Bus\Command;
use App\Application\Activity\Bus\CommandHandlerNotFound;
use App\Application\Activity\Bus\MappedCommandBus;
use Tests\TestCase;

final class MappedCommandBusTest extends TestCase
{
    public function test_registered_handler_receives_command_and_returns_result(): void
    {
        $command = new class('ACT-001') implements Command
        {
            public function __construct(public readonly string $code) {}
        };
        $commandBus = new MappedCommandBus([
            $command::class => fn (Command $received): string => $received->code,
        ]);

        $result = $commandBus->dispatch($command);

        self::assertSame('ACT-001', $result);
    }

    public function test_unregistered_command_is_rejected(): void
    {
        $command = new class implements Command {};
        $commandBus = new MappedCommandBus([]);

        $this->expectException(CommandHandlerNotFound::class);
        $this->expectExceptionMessage($command::class);

        $commandBus->dispatch($command);
    }
}
