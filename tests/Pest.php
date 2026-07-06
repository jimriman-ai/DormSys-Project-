<?php

declare(strict_types=1);
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

require_once __DIR__.'/Architecture/architecture.php';
require_once __DIR__.'/Feature/Modules/Request/support/mutation-principal.php';
require_once __DIR__.'/Feature/Modules/Request/support/http-mutation.php';
require_once __DIR__.'/Feature/Modules/CheckIn/support/mutation-principal.php';

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Architecture');

pest()->group('architecture')->in('Architecture');
