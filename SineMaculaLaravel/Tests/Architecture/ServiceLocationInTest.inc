<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use PHPUnit\Framework\TestCase;

final class PaymentServiceTest extends TestCase
{
    public function testResolves(): void
    {
        $logger = app(Logger::class);
        $other  = resolve(Logger::class);
        $made   = App::make(Logger::class);
    }
}
