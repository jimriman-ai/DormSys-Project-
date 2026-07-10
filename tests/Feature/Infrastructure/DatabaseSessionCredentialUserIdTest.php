<?php

declare(strict_types=1);

use App\Infrastructure\Session\CredentialUserDatabaseSessionHandler;
use Database\Seeders\DevelopmentUserSeeder;
use Database\Seeders\IdentityRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['session.driver' => 'database']);

    app()->forgetInstance('session.store');

    app()->instance('session.store', app('session')->driver('database'));

    Artisan::call('db:seed', ['--class' => IdentityRoleSeeder::class]);
    Artisan::call('db:seed', ['--class' => DevelopmentUserSeeder::class]);
});

it('persists database session metadata with credential user id on protected routes', function (): void {
    $this->post('/login', [
        'identifier' => DevelopmentUserSeeder::EMPLOYEE_EMAIL,
        'password' => DevelopmentUserSeeder::EMPLOYEE_PASSWORD,
    ])->assertRedirect(route('requests.index'));

    $response = $this->get('/requests');

    $response->assertOk()->assertSee('درخواست‌های من');

    expect(session()->getHandler())->toBeInstanceOf(CredentialUserDatabaseSessionHandler::class);

    $apiPrincipalId = auth('api')->id();
    expect($apiPrincipalId)->toBeString()
        ->and($apiPrincipalId)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');

    $sessionCookie = $response->getCookie(config('session.cookie'));
    expect($sessionCookie)->not->toBeNull();

    if ($sessionCookie === null) {
        $this->fail('Expected session cookie to be present.');
    }

    $sessionRow = DB::table('sessions')
        ->where('id', $sessionCookie->getValue())
        ->first();

    if ($sessionRow === null) {
        $sessionRow = DB::table('sessions')->orderByDesc('last_activity')->first();
    }

    expect($sessionRow)->not->toBeNull();

    if ($sessionRow === null) {
        $this->fail('Expected session row to be present.');
    }

    $credentialUserId = $sessionRow->user_id;
    expect($credentialUserId)->not->toBeNull()
        ->and(ctype_digit((string) $credentialUserId))->toBeTrue()
        ->and((string) $credentialUserId)->not->toBe($apiPrincipalId);
});
