<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Architecture;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsTestClasses;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesNamespace;
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
#[CoversTrait(DetectsFunctionCalls::class)]
#[CoversTrait(DetectsTestClasses::class)]
#[CoversTrait(ResolvesNamespace::class)]
final class DisallowServiceLocationSniffTest extends AbstractSniffTestCase
{
    /**
     * Container helpers and the App::make/App::makeWith facade with a literal
     * class are flagged inside a class; injected dependencies, helpers outside
     * a class, same-named methods, other App facade calls and dynamic
     * resolution of a runtime variable are not.
     *
     * @return void
     */
    public function testFlagsServiceLocationInClassBodies(): void
    {
        $this->assertErrorsOnLines('DisallowServiceLocation.inc', [11, 16, 21, 55, 78]);
    }

    /**
     * The error names the offending call: a helper by its bare name, a facade
     * resolution with the App:: prefix.
     *
     * @return void
     */
    public function testReportsHelperAndFacadeMessages(): void
    {
        $this->assertErrorMessagesOnLines('DisallowServiceLocation.inc', [
            11 => ['Service location ("app()") is not allowed in a class body; inject the dependency instead.'],
            16 => ['Service location ("resolve()") is not allowed in a class body; inject the dependency instead.'],
            21 => ['Service location ("App::make()") is not allowed in a class body; inject the dependency instead.'],
            55 => ['Service location ("app()") is not allowed in a class body; inject the dependency instead.'],
            78 => ['Service location ("App::makeWith()") is not allowed in a class body; inject the dependency instead.'],
        ]);
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
     * A class in a wiring namespace is exempt even without a wiring suffix or
     * base class.
     *
     * @return void
     */
    public function testExemptsWiringNamespaceWithoutSuffixOrBase(): void
    {
        $this->assertErrorsOnLines('ProviderNamespaceOnly.inc', []);
    }

    /**
     * A class extending a fully qualified wiring base is exempt outside a
     * wiring namespace and without a wiring suffix.
     *
     * @return void
     */
    public function testExemptsQualifiedWiringBaseClass(): void
    {
        $this->assertErrorsOnLines('QualifiedBaseProvider.inc', []);
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
