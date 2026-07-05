<?php

declare(strict_types=1);

use App\Modules\Identity\Infrastructure\Persistence\Models\UserModel;

it('rejects password lookup because identity users are not credential-authenticatable', function (): void {
    $model = new UserModel;

    expect(fn (): string => $model->getAuthPassword())
        ->toThrow(LogicException::class, 'Identity users do not support password-based authentication');
});

it('does not expose a remember-token column for identity users', function (): void {
    $model = new UserModel;

    expect($model->getRememberTokenName())->toBe('');
    expect($model->getRememberToken())->toBeNull();
});
