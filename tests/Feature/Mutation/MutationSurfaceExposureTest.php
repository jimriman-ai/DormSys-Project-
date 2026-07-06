<?php

declare(strict_types=1);

use App\Application\Mutation\Exceptions\UnauthorizedMutationException;
use App\Application\Mutation\Support\MutationPrincipalContextHolder;
use App\Modules\Identity\Application\Services\CreateUserAction;
use App\Modules\Identity\Presentation\Console\CreateUserCommand;
use App\Modules\Lottery\Infrastructure\Jobs\AutoLockLotteryJob;
use App\Modules\Request\Presentation\Http\Controllers\RequestMutationController;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(MutationPrincipalContextHolder::class)->clear();
    request()->attributes->remove('audit_principal_user_id');
});

it('exposes only action delegation from the known HTTP mutation controller', function (): void {
    $reflection = new ReflectionClass(RequestMutationController::class);
    $constructor = $reflection->getConstructor();
    expect($constructor)->not->toBeNull();
    $constructor = $constructor ?? throw new RuntimeException('RequestMutationController constructor missing.');

    $parameters = array_map(
        static fn (ReflectionParameter $parameter): string => (string) $parameter->getType(),
        $constructor->getParameters(),
    );

    foreach ($parameters as $type) {
        expect($type)->toContain('Action');
    }
});

it('fails closed for adopted CLI surfaces without principal establishment', function (): void {
    expect(fn () => app(CreateUserAction::class)->execute('Exposure Audit', 'exposure@example.com'))
        ->toThrow(UnauthorizedMutationException::class);

    expect(fn () => $this->artisan(CreateUserCommand::class, [
        'display_name' => 'Exposure Audit',
        '--email' => 'exposure-cli@example.com',
    ]))->toThrow(UnauthorizedMutationException::class);
});

it('does not allow adopted runtime jobs to be alternate authorization layers', function (): void {
    $path = (new ReflectionClass(AutoLockLotteryJob::class))->getFileName();
    expect($path)->not->toBeFalse();

    $contents = file_get_contents((string) $path);
    expect($contents)->toBeString()
        ->and($contents)->not->toContain('MutationPolicyEnforcementPoint')
        ->and($contents)->not->toContain('assertManageProgram')
        ->and($contents)->toContain('runJobAsSystem');
});
