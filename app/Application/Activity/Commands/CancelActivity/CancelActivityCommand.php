<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\CancelActivity;

use App\Application\Activity\Bus\Command;

final readonly class CancelActivityCommand implements Command
{
    public function __construct(public string $activityId) {}
}
