<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Structure\RequireRoleDirectorySniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the role directory sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(RequireRoleDirectorySniff::class)]
final class RequireRoleDirectorySniffTest extends AbstractSniffTestCase
{
    /**
     * A class whose role is resolved by identity must live under that role's
     * location; a controller or model in the service layer is flagged.
     *
     * @return void
     */
    public function testFlagsRoleClassesOutsideTheirLocation(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryMisplaced.inc', [8, 12]);
    }

    /**
     * Nested role directories and classes resolved by location are placed and
     * therefore clean; an abstract base is never located.
     *
     * @return void
     */
    public function testAllowsNestedAndLocationResolvedClasses(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryPlaced.inc', []);
    }

    /**
     * Classes in an exempt sub-namespace (and traits) are never placed.
     *
     * @return void
     */
    public function testIgnoresExemptSubNamespaces(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryConcerns.inc', []);
    }

    /**
     * An entry-point provider may live at the package/module root.
     *
     * @return void
     */
    public function testAllowsAProviderAtTheModuleRoot(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryModuleRoot.inc', []);
    }

    /**
     * A module-root role under another role's location is still flagged.
     *
     * @return void
     */
    public function testFlagsAModuleRootRoleUnderAnotherLocation(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryProviderMisplaced.inc', [7]);
    }

    /**
     * A class marked `@role-exempt` is never flagged.
     *
     * @return void
     */
    public function testRespectsTheEscapeHatch(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryExempt.inc', []);
    }
}
