# Contract: Module Service Provider

**Version**: 1.0.0 | **Spec**: Spec01 Foundation

## Purpose

Defines the registration contract each DormSys bounded-context module must follow via its service provider.

## Interface

```php
namespace App\Shared\Infrastructure;

abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract protected function moduleName(): string;

    public function register(): void;
    public function boot(): void;
}
```

## Registration Rules

| Rule | Description |
|------|-------------|
| MSP-01 | Each module MUST have exactly one `{Module}ServiceProvider` |
| MSP-02 | Provider MUST be registered in `bootstrap/providers.php` |
| MSP-03 | `boot()` MAY call `loadMigrationsFrom(database_path("migrations/{snake_module}"))` |
| MSP-04 | `register()` MUST only bind interfaces to implementations within the same module |
| MSP-05 | Providers MUST NOT register routes with business logic in Spec01 (stubs only) |

## Module Provider Registry (Foundation)

| Module | Provider Class | Migration Path |
|--------|----------------|----------------|
| Identity | `App\Modules\Identity\IdentityServiceProvider` | `database/migrations/identity/` |
| Employee | `App\Modules\Employee\EmployeeServiceProvider` | `database/migrations/employee/` |
| Request | `App\Modules\Request\RequestServiceProvider` | `database/migrations/request/` |
| Approval | `App\Modules\Approval\ApprovalServiceProvider` | `database/migrations/approval/` |
| Dormitory | `App\Modules\Dormitory\DormitoryServiceProvider` | `database/migrations/dormitory/` |
| Allocation | `App\Modules\Allocation\AllocationServiceProvider` | `database/migrations/allocation/` |
| Lottery | `App\Modules\Lottery\LotteryServiceProvider` | `database/migrations/lottery/` |
| Voucher | `App\Modules\Voucher\VoucherServiceProvider` | `database/migrations/voucher/` |
| Notification | `App\Modules\Notification\NotificationServiceProvider` | `database/migrations/notification/` |
| Audit | `App\Modules\Audit\AuditServiceProvider` | `database/migrations/audit/` |

## Constitutional Alias

`Approval` module provider implements the `Workflow` bounded context per Constitution Section 11.
