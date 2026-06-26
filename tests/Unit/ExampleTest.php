<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Models\BaseModel;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_support_kernel_base_model_is_available(): void
    {
        $this->assertTrue(class_exists(BaseModel::class));
    }
}
