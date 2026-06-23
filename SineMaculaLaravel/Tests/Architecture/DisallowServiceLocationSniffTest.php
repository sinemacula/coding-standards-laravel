<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Architecture;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Architecture\DisallowServiceLocationSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the service location sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowServiceLocationSniff::class)]
final class DisallowServiceLocationSniffTest extends AbstractSniffTestCase
{
    /**
     * Container helpers and the App::make facade with a literal class are
     * flagged inside a class; injected dependencies, helpers outside a class,
     * and dynamic resolution of a runtime variable are not.
     *
     * @return void
     */
    public function testFlagsServiceLocationInClassBodies(): void
    {
        $this->assertErrorsOnLines('DisallowServiceLocation.inc', [11, 16, 21, 55]);
    }

    /**
     * Container-wiring classes - a service provider (by namespace) and a
     * registrar (by suffix or base class) - may use the container.
     *
     * @return void
     */
    public function testExemptsContainerWiringClasses(): void
    {
        $this->assertErrorsOnLines('Provider.inc', []);
        $this->assertErrorsOnLines('Registrar.inc', []);
    }

    /**
     * Test code may resolve services from the container to assert on them.
     *
     * @return void
     */
    public function testExemptsTestFiles(): void
    {
        $this->assertErrorsOnLines('ServiceLocationInTest.inc', []);
    }
}
