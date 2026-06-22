<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
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
final class RequireRoleNamingSniffTest extends AbstractSniffTestCase
{
    /** @var array<string, mixed> Per-test property overrides for the sniff. */
    private array $overrides = [];

    /**
     * A class is named by its role: a controller must end with `Controller`,
     * a model must not end with `Model`/`Entity`, and free roles (commands,
     * jobs, mailables) carry no constraint. Detection is by identity.
     *
     * @return void
     */
    public function testFlagsNamesAgainstResolvedRole(): void
    {
        $this->assertErrorsOnLines('RoleNaming.inc', [13, 25]);
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
