<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Application\Contracts\UnitOfWork;
use App\Domain\Activity\Entities\Activity;
use App\Domain\Activity\Enums\ActivityPriority;
use App\Domain\Activity\Repositories\ActivityRepository;
use App\Domain\Activity\ValueObjects\ActivityCode;
use App\Domain\Activity\ValueObjects\ActivityId;
use App\Domain\Activity\ValueObjects\ActivitySchedule;
use App\Domain\Activity\ValueObjects\ActivityTitle;
use App\Domain\Activity\ValueObjects\ResponsibleId;
use DateTimeImmutable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

final class MySqlUnitOfWorkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('RUN_MYSQL_INTEGRATION') !== '1') {
            $this->markTestSkipped('Defina RUN_MYSQL_INTEGRATION=1 para ejecutar la integración MySQL.');
        }

        $database = (string) (getenv('MYSQL_TEST_DATABASE') ?: 'mineops_planning_test');

        if (! str_ends_with($database, '_test')) {
            self::fail('MYSQL_TEST_DATABASE debe terminar en _test para proteger bases no destinadas a pruebas.');
        }

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => getenv('MYSQL_TEST_HOST') ?: '127.0.0.1',
            'database.connections.mysql.port' => getenv('MYSQL_TEST_PORT') ?: '3306',
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => getenv('MYSQL_TEST_USERNAME') ?: 'root',
            'database.connections.mysql.password' => getenv('MYSQL_TEST_PASSWORD') ?: '',
        ]);

        DB::purge('mysql');
        Artisan::call('migrate:fresh', ['--database' => 'mysql', '--force' => true]);
    }

    public function test_activities_use_innodb_and_transaction_rolls_back_on_failure(): void
    {
        $database = (string) config('database.connections.mysql.database');
        $table = DB::connection('mysql')->selectOne(
            'SELECT ENGINE AS engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$database, 'activities'],
        );

        self::assertNotNull($table);
        self::assertSame('InnoDB', $table->engine);

        $activity = $this->activity();

        try {
            $this->app->make(UnitOfWork::class)->transactional(function () use ($activity): void {
                $this->app->make(ActivityRepository::class)->save($activity);

                throw new RuntimeException('Forzar rollback.');
            });
        } catch (RuntimeException $exception) {
            self::assertSame('Forzar rollback.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('activities', ['id' => $activity->id()->value()], 'mysql');
    }

    private function activity(): Activity
    {
        return Activity::create(
            new ActivityId('00000000-0000-4000-8000-000000000001'),
            new ActivityCode('ACT-MYSQL-001'),
            new ActivityTitle('Prueba transaccional MySQL'),
            null,
            'Operaciones',
            'Frente norte',
            new ResponsibleId('d7794c47-cb29-4f1b-ab36-e5f7568a52fe'),
            ActivityPriority::HIGH,
            new ActivitySchedule(
                new DateTimeImmutable('2026-10-10'),
                new DateTimeImmutable('2026-10-11'),
            ),
        );
    }
}
