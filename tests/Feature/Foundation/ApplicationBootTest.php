<?php

namespace Tests\Feature\Foundation;

use Tests\TestCase;

class ApplicationBootTest extends TestCase
{
    public function test_application_health_endpoint_returns_ok(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }
}
