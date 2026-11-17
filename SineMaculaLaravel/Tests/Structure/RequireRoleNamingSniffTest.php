<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDeclarationNames;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsRelatedNames;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesImports;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesNamespace;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesRole;
use SineMaculaLaravel\Sniffs\Structure\RequireRoleNamingSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the role naming sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(RequireRoleNamingSniff::class)]
#[CoversTrait(ReadsDeclarationNames::class)]
#[CoversTrait(ReadsRelatedNames::class)]
#[CoversTrait(ResolvesImports::class)]
#[CoversTrait(ResolvesNamespace::class)]
#[CoversTrait(ResolvesRole::class)]
final class RequireRoleNamingSniffTest extends AbstractSniffTestCase
{
    /** @var array<string, mixed> Per-test property overrides for the sniff. */
    private array $overrides = [];

    /**
     * Flags names that break the constraints of their resolved role.
     *
     * @return void
     */
    public function testFlagsNamesAgainstResolvedRole(): void
    {
        $this->assertErrorsOnLines('RoleNaming.inc', [13, 25, 61, 70]);
    }

    /**
     * A class marked `@role-exempt` or `#[NotARole]` is never flagged.
     *
     * @return void
     */
    public function testRespectsTheEscapeHatch(): void
    {
        $this->assertErrorsOnLines('RoleNamingExempt.inc', []);
    }

    /**
     * A ruleset can tighten a free role: requiring a `Job` suffix is honoured.
     *
     * @return void
     */
    public function testHonoursAConfiguredSuffix(): void
    {
        $this->overrides = ['requireSuffix' => ['Job' => 'Job']];

        $this->assertErrorsOnLines('RoleNamingJobOverride.inc', [7]);
    }

    /**
     * Resolves identity through configured project bases.
     *
     * @return void
     */
    public function testResolvesIdentityThroughConfiguredBases(): void
    {
        $this->overrides = ['roleIdentities' => ['Controller' => 'Controller,BaseController']];

        $this->assertErrorsOnLines('RoleNamingInheritance.inc', [5]);
    }

    /**
     * Never flags free roles resolved only by location.
     *
     * @return void
     */
    public function testLeavesLocationResolvedFreeRolesBare(): void
    {
        $this->assertErrorsOnLines('RoleNamingFree.inc', []);
    }

    /**
     * Resolves a concrete class's role from its namespace location.
     *
     * @return void
     */
    public function testResolvesARoleFromTheClassLocation(): void
    {
        $this->assertErrorsOnLines('RoleNamingLocation.inc', [5]);
    }

    /**
     * A class in an exempt sub-namespace of a role location resolves no role.
     *
     * @return void
     */
    public function testIgnoresExemptSubNamespacesForLocation(): void
    {
        $this->assertErrorsOnLines('RoleNamingConcerns.inc', []);
    }

    /**
     * Resolves identity from trait use in every declaration shape.
     *
     * @return void
     */
    public function testResolvesIdentityFromTraitUse(): void
    {
        $this->overrides = [
            'roleIdentities' => ['Job' => 'ShouldQueue, Dispatchable'],
            'requireSuffix'  => ['Job' => 'Job'],
        ];

        $this->assertErrorsOnLines('RoleNamingTraitIdentity.inc', [8, 13, 19, 25, 30, 35]);
    }

    /**
     * Resolves identity from class attributes, skipping their arguments.
     *
     * @return void
     */
    public function testResolvesIdentityFromAttributes(): void
    {
        $this->overrides = [
            'roleIdentities' => ['Command' => 'AsCommand'],
            'requireSuffix'  => ['Command' => 'Command'],
        ];

        $this->assertErrorsOnLines('RoleNamingAttributes.inc', [6, 12, 22]);
    }

    /**
     * Look-alikes of the escape hatches do not opt a class out.
     *
     * @return void
     */
    public function testFlagsClassesThatOnlyResembleExemptions(): void
    {
        $this->assertErrorsOnLines('RoleNamingNotExempt.inc', [17, 22, 30]);
    }

    /**
     * A test class plays no role, even under a mirrored role namespace.
     *
     * @return void
     */
    public function testExemptsTestClassesFromRoles(): void
    {
        $this->assertErrorsOnLines('RoleNamingTestMirror.inc', [15]);
    }

    /**
     * Every class in a file under a tests/ directory plays no role.
     *
     * @return void
     */
    public function testExemptsFilesUnderATestsDirectory(): void
    {
        $this->assertErrorsOnLines('tests/RoleNamingTestPath.inc', []);
    }

    /**
     * Only the first matching forbidden suffix is reported for a class.
     *
     * @return void
     */
    public function testReportsOneForbiddenSuffixPerClass(): void
    {
        $this->overrides = ['forbidSuffix' => ['Model' => 'DataModel,Model']];

        $this->assertErrorMessagesOnLines('RoleNamingForbiddenOverride.inc', [
            7 => ['A Model class must not be named with a "DataModel" suffix; Laravel names it bare.'],
        ]);
    }

    /**
     * Scopes the docblock escape hatch to the class it is attached to.
     *
     * @return void
     */
    public function testScopesTheDocblockExemptionToItsOwnClass(): void
    {
        $this->assertErrorsOnLines('RoleNamingDocblockExempt.inc', [15, 19]);
    }

    /**
     * Property overrides applied to the sniff under test.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function sniffProperties(): array
    {
        return $this->overrides;
    }
}
