<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
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
#[CoversTrait(ResolvesNamespace::class)]
#[CoversTrait(ResolvesRole::class)]
final class RequireRoleNamingSniffTest extends AbstractSniffTestCase
{
    /** @var array<string, mixed> Per-test property overrides for the sniff. */
    private array $overrides = [];

    /**
     * A class is named by its role: a controller must end with `Controller`, a
     * model must not end with `Model`/`Entity`, and free roles (commands, jobs,
     * mailables) carry no constraint. Detection is by identity.
     *
     * @return void
     */
    public function testFlagsNamesAgainstResolvedRole(): void
    {
        $this->assertErrorsOnLines('RoleNaming.inc', [13, 25, 61]);
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
     * Identity resolves against configured bases, covering a project's own base
     * the short-name match cannot follow through inheritance.
     *
     * @return void
     */
    public function testResolvesIdentityThroughConfiguredBases(): void
    {
        $this->overrides = ['roleIdentities' => ['Controller' => 'Controller,BaseController']];

        $this->assertErrorsOnLines('RoleNamingInheritance.inc', [5]);
    }

    /**
     * Free roles resolved only by location (listeners, events, middleware) are
     * bare and never flagged.
     *
     * @return void
     */
    public function testLeavesLocationResolvedFreeRolesBare(): void
    {
        $this->assertErrorsOnLines('RoleNamingFree.inc', []);
    }

    /**
     * A concrete class with no identity resolves its role from the namespace
     * location; an abstract class in the same location never does.
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
     * Trait use resolves identity in every declaration shape: single and
     * repeated statements, grouped and fully-qualified names, and a compact
     * class body.
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
     * Class attributes resolve identity, skipping their arguments and working
     * across stacked groups and a final class.
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
     * A docblock without the exempt tag, or an attribute argument merely named
     * NotARole, does not opt a class out.
     *
     * @return void
     */
    public function testFlagsClassesThatOnlyResembleExemptions(): void
    {
        $this->assertErrorsOnLines('RoleNamingNotExempt.inc', [17, 22]);
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
     * The docblock escape hatch reads only the docblock attached to the class:
     * a following bare class is still flagged, and an implements-only identity
     * still resolves its role.
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
