<?php

declare(strict_types=1);

namespace App\Application\Activity\Commands\CompleteActivity;

use App\Application\Activity\Bus\Command;

final readonly class CompleteActivityCommand implements Command
{
    public function __construct(public string $activityId) {}
}
