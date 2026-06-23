<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Modules\Allocation\Infrastructure\Providers\AllocationServiceProvider;
use App\Modules\Audit\Infrastructure\Providers\AuditServiceProvider;
use App\Modules\Dormitory\Infrastructure\Providers\DormitoryServiceProvider;
use App\Modules\Employee\Infrastructure\Providers\EmployeeServiceProvider;
use App\Modules\Identity\Infrastructure\Providers\IdentityServiceProvider;
use App\Modules\Lottery\Infrastructure\Providers\LotteryServiceProvider;
use App\Modules\Notification\Infrastructure\Providers\NotificationServiceProvider;
use App\Modules\Reporting\Infrastructure\Providers\ReportingServiceProvider;
use App\Modules\Request\Infrastructure\Providers\RequestServiceProvider;
use App\Modules\Voucher\Infrastructure\Providers\VoucherServiceProvider;
use App\Modules\Workflow\Infrastructure\Providers\WorkflowServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModuleMigrationPathsTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public static function moduleProviderPaths(): array
    {
        return [
            'identity' => [IdentityServiceProvider::class, 'identity'],
            'employee' => [EmployeeServiceProvider::class, 'employee'],
            'request' => [RequestServiceProvider::class, 'request'],
            'workflow' => [WorkflowServiceProvider::class, 'workflow'],
            'dormitory' => [DormitoryServiceProvider::class, 'dormitory'],
            'allocation' => [AllocationServiceProvider::class, 'allocation'],
            'lottery' => [LotteryServiceProvider::class, 'lottery'],
            'voucher' => [VoucherServiceProvider::class, 'voucher'],
            'notification' => [NotificationServiceProvider::class, 'notification'],
            'audit' => [AuditServiceProvider::class, 'audit'],
            'reporting' => [ReportingServiceProvider::class, 'reporting'],
        ];
    }

    #[Test]
    #[DataProvider('moduleProviderPaths')]
    public function it_registers_module_owned_migration_directories(string $providerClass, string $module): void
    {
        $expected = database_path('migrations/modules/'.$module);

        $this->assertDirectoryExists($expected);
        $this->assertStringContainsString(
            "loadMigrationsFrom(database_path('migrations/modules/{$module}'))",
            (string) file_get_contents((new \ReflectionClass($providerClass))->getFileName())
        );
    }

    #[Test]
    public function it_provides_a_module_migration_stub_template(): void
    {
        $stub = (string) file_get_contents(base_path('stubs/migration.module.stub'));

        $this->assertStringContainsString("\$table->uuid('id')->primary();", $stub);
        $this->assertStringContainsString('$table->timestamps();', $stub);
        $this->assertStringContainsString('$table->softDeletes();', $stub);
        $this->assertStringContainsString("\$table->uuid('created_by')->nullable();", $stub);
        $this->assertStringContainsString("\$table->uuid('updated_by')->nullable();", $stub);
        $this->assertStringContainsString("\$table->uuid('deleted_by')->nullable();", $stub);
    }
}
