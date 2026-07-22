<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesImports;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesNamespace;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesRole;
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
#[CoversTrait(ResolvesImports::class)]
#[CoversTrait(ResolvesNamespace::class)]
#[CoversTrait(ResolvesRole::class)]
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

    /**
     * An idiomatic event using the Events `Dispatchable` trait is not a Job:
     * the qualified Job identity resolves through the file's imports, so the
     * short-name collision with the Bus trait never fires.
     *
     * @return void
     */
    public function testLeavesEventsDistinctFromJobs(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryEventDispatch.inc', []);
    }

    /**
     * A sync job is identified through every import shape - plain, aliased,
     * grouped, namespace-headed and fully-qualified - while the aliased event
     * trait, a relative unimported name, a same-suffix segment (MiniBus), and
     * function/constant imports of the colliding name all stay out of the Job
     * identity.
     *
     * @return void
     */
    public function testResolvesJobIdentityThroughImports(): void
    {
        $this->assertErrorsOnLines('RoleDirectorySyncJob.inc', [15, 20, 25, 30, 35]);
    }

    /**
     * A trait `use` inside an earlier class body never leaks into the import
     * map of a later class, and an import between classes still resolves.
     *
     * @return void
     */
    public function testKeepsClassBodyUseOutOfTheImportMap(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryTraitUseIsolation.inc', [5]);
    }

    /**
     * The misplaced message names the role and its directory in slash form.
     *
     * @return void
     */
    public function testRendersTheMisplacedMessage(): void
    {
        $this->assertErrorMessagesOnLines('RoleDirectoryMisplaced.inc', [
            8  => ['A Controller class must live under a "Http/Controllers" directory.'],
            12 => ['A Model class must live under a "Models" directory.'],
        ]);
    }
}
