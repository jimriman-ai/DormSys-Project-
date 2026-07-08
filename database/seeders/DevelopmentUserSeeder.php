<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\CheckIn\Domain\CheckInOperationRoles;
use App\Support\Development\DevelopmentUserAccountReport;
use App\Support\Development\DevelopmentUserProvisioner;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Local development accounts only.
 *
 * Run manually:
 *   php artisan db:seed --class=DevelopmentUserSeeder
 *
 * Do not register in DatabaseSeeder — production deployments must not invoke this seeder.
 */
class DevelopmentUserSeeder extends Seeder
{
    public const string EMPLOYEE_EMAIL = 'dev.employee@dormsys.local';

    public const string EMPLOYEE_PASSWORD = 'dev-employee-password';

    public const string APPROVER_EMAIL = 'dev.approver@dormsys.local';

    public const string APPROVER_PASSWORD = 'dev-approver-password';

    public const string ADMIN_EMAIL = 'dev.admin@dormsys.local';

    public const string ADMIN_PASSWORD = 'dev-admin-password';

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('DevelopmentUserSeeder skipped: intended for local/testing environments only.');

            return;
        }

        $this->call(IdentityRoleSeeder::class);
        $this->ensureOperatorRoleExists();

        $reports = app(DevelopmentUserProvisioner::class)->provision($this->accounts());

        $this->command?->info('Development users provisioned (idempotent).');
        $this->command?->newLine();

        foreach ($reports as $report) {
            $this->printReport($report);
        }

        $this->command?->newLine();
        $this->command?->warn('These credentials are for local development only. Never use in production.');
    }

    /**
     * @return list<array{
     *     label: string,
     *     display_name: string,
     *     email: string,
     *     password: string,
     *     roles: list<string>,
     *     employee?: array{
     *         code: string,
     *         first_name: string,
     *         last_name: string,
     *         national_code: string,
     *         hire_date: string,
     *     }|null
     * }>
     */
    private function accounts(): array
    {
        return [
            [
                'label' => 'System Administrator',
                'display_name' => 'Dev Admin',
                'email' => self::ADMIN_EMAIL,
                'password' => self::ADMIN_PASSWORD,
                'roles' => [IdentityRoleSeeder::ROLE_SYSTEM_ADMINISTRATOR],
                'employee' => null,
            ],
            [
                'label' => 'Employee / Request Owner',
                'display_name' => 'Dev Employee',
                'email' => self::EMPLOYEE_EMAIL,
                'password' => self::EMPLOYEE_PASSWORD,
                'roles' => [],
                'employee' => [
                    'code' => 'DEV-EMP-001',
                    'first_name' => 'Dev',
                    'last_name' => 'Employee',
                    'national_code' => '0499370899',
                    'hire_date' => '2024-01-01',
                ],
            ],
            [
                'label' => 'Approver (Administrator role)',
                'display_name' => 'Dev Approver',
                'email' => self::APPROVER_EMAIL,
                'password' => self::APPROVER_PASSWORD,
                'roles' => [IdentityRoleSeeder::ROLE_ADMINISTRATOR],
                'employee' => null,
            ],
        ];
    }

    private function ensureOperatorRoleExists(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate(CheckInOperationRoles::OPERATOR, config('auth.defaults.guard', 'web'));
    }

    private function printReport(DevelopmentUserAccountReport $report): void
    {
        $this->command?->line("<fg=cyan>{$report->label}</>");
        $this->command?->line("  Email: {$report->email}");
        $this->command?->line("  Password: {$report->password}");
        $this->command?->line('  Credential user: '.($report->credentialCreated ? 'created' : 'already existed'));
        $this->command?->line("  Identity ID: {$report->identityId} (".($report->identityCreated ? 'created' : 'already existed').')');

        if ($report->employeeId !== null) {
            $this->command?->line("  Employee ID: {$report->employeeId} (".($report->employeeCreated ? 'created' : 'already existed').')');
        } else {
            $this->command?->line('  Employee: none');
        }

        $roles = $report->roles === [] ? 'none' : implode(', ', $report->roles);
        $this->command?->line("  Roles: {$roles}");
        $this->command?->newLine();
    }
}
