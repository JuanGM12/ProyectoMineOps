<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\StartActivity;

use App\Application\Activity\Bus\Command;

final readonly class StartActivityCommand implements Command
{
    public function __construct(public string $activityId) {}
}
