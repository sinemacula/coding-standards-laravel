<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Architecture;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsTestClasses;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesImports;
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
#[CoversTrait(ResolvesImports::class)]
#[CoversTrait(ResolvesNamespace::class)]
final class DisallowServiceLocationSniffTest extends AbstractSniffTestCase
{
    /** @var array<int, string>|null Uninjectable bases for the sniff under test, or null for the default. */
    private ?array $uninjectableBaseClasses = null;

    /**
     * Flags container helpers and App::make/makeWith inside a class body.
     *
     * @return void
     */
    public function testFlagsServiceLocationInClassBodies(): void
    {
        $this->assertErrorsOnLines('DisallowServiceLocation.inc', [11, 16, 21, 55, 78]);
    }

    /**
     * Names a helper by its bare name and a facade call with its App:: prefix.
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
     * Exempts service providers and registrars, which wire the container.
     *
     * @return void
     */
    public function testExemptsContainerWiringClasses(): void
    {
        $this->assertErrorsOnLines('Provider.inc', []);
        $this->assertErrorsOnLines('Registrar.inc', []);
    }

    /**
     * Exempts a wiring-namespace class without a wiring suffix or base class.
     *
     * @return void
     */
    public function testExemptsWiringNamespaceWithoutSuffixOrBase(): void
    {
        $this->assertErrorsOnLines('ProviderNamespaceOnly.inc', []);
    }

    /**
     * Exempts a class extending a fully qualified wiring base.
     *
     * @return void
     */
    public function testExemptsQualifiedWiringBaseClass(): void
    {
        $this->assertErrorsOnLines('QualifiedBaseProvider.inc', []);
    }

    /**
     * Exempts model factories, which have no injection point.
     *
     * @return void
     */
    public function testExemptsClassesTheFrameworkConstructs(): void
    {
        $this->assertErrorsOnLines('Factory.inc', []);
    }

    /**
     * Exempts API resources, which have no injection point.
     *
     * @return void
     */
    public function testExemptsApiResources(): void
    {
        $this->assertErrorsOnLines('Resource.inc', []);
    }

    /**
     * A project class sharing the toolkit resource's short name is not exempt.
     *
     * @return void
     */
    public function testDoesNotExemptAnUnrelatedApiResource(): void
    {
        $this->assertErrorsOnLines('UnrelatedApiResource.inc', [15]);
    }

    /**
     * Does not exempt an unrelated base such as Illuminate\View\Factory.
     *
     * @return void
     */
    public function testDoesNotExemptAnUnrelatedBaseOfTheSameShortName(): void
    {
        $this->assertErrorsOnLines('ViewFactory.inc', [13, 20]);
    }

    /**
     * Exempts a factory behind a configured intermediate base.
     *
     * @return void
     */
    public function testExemptsAFactoryBehindAConfiguredIntermediateBase(): void
    {
        $this->uninjectableBaseClasses = ['Illuminate\Database\Eloquent\Factories\Factory', 'BaseFactory'];

        $this->assertErrorsOnLines('FactoryIntermediateBase.inc', []);
    }

    /**
     * Reports a factory behind an unconfigured intermediate base.
     *
     * @return void
     */
    public function testReportsAFactoryBehindAnUnconfiguredIntermediateBase(): void
    {
        $this->assertErrorsOnLines('FactoryIntermediateBase.inc', [12]);
    }

    /**
     * Accepts a configured base written with a leading separator.
     *
     * @return void
     */
    public function testAcceptsAConfiguredBaseWrittenFullyQualified(): void
    {
        $this->uninjectableBaseClasses = ['\Illuminate\Database\Eloquent\Factories\Factory'];

        $this->assertErrorsOnLines('Factory.inc', []);
    }

    /**
     * Matches qualified configured bases on whole namespace segments.
     *
     * @return void
     */
    public function testMatchesQualifiedBasesOnSegmentBoundaries(): void
    {
        $this->uninjectableBaseClasses = ['Factories\Factory'];

        $this->assertErrorsOnLines('PartialSegmentFactory.inc', [11]);
    }

    /**
     * Exempts only the failed() hook the framework calls on a queued payload.
     *
     * @return void
     */
    public function testExemptsTheFailedHookOfAQueuedPayload(): void
    {
        $this->assertErrorsOnLines('QueuedFailedHook.inc', [15, 54, 65, 74, 85]);
    }

    /**
     * Does not exempt failed() on a container-built queued listener.
     *
     * @return void
     */
    public function testDoesNotExemptTheFailedHookOfAQueuedListener(): void
    {
        $this->assertErrorsOnLines('QueuedListenerFailedHook.inc', [13]);
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

    /**
     * Property overrides to apply to the sniff under test.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function sniffProperties(): array
    {
        return $this->uninjectableBaseClasses === null
            ? []
            : ['uninjectableBaseClasses' => $this->uninjectableBaseClasses];
    }
}
