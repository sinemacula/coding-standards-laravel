<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesRole;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for role resolution itself, rather than for either sniff that reports
 * it. The directory sniff is driven because it names the resolved role in its
 * message, so a test can assert which role was chosen and not merely that some
 * role was.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversTrait(ResolvesRole::class)]
final class ResolvesRoleTest extends AbstractSniffTestCase
{
    /** @var array<string, mixed> Per-test property overrides for the sniff. */
    private array $overrides = [];

    /**
     * An attribute naming another class does not make the subject that class. A
     * repository attributed with the model it serves keeps its own identity,
     * while a genuine Eloquent model in the same file is still placed - even
     * imported under an alias, which the qualified identity follows and a bare
     * short name would miss.
     *
     * @return void
     */
    public function testLeavesAClassAttributedWithAForeignModel(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryForeignAttribute.inc', [15]);
    }

    /**
     * What a class declares outranks what it is attributed with, whichever the
     * identity table happens to list first.
     *
     * @return void
     */
    public function testPrefersDeclaredIdentityOverAnAttribute(): void
    {
        $this->overrides = ['roleIdentities' => ['Command' => 'AsCommand', 'Controller' => 'Controller']];

        $this->assertErrorMessagesOnLines('RoleDirectoryDeclaredBeatsAttribute.inc', [
            8 => ['A Controller class must live under a "Http/Controllers" directory.'],
        ]);
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
     * A bare identity is matched against the trailing segment of the name as
     * written, so a parent named in full still resolves its role.
     *
     * @return void
     */
    public function testMatchesABareIdentityAgainstAQualifiedDeclaration(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryQualifiedDeclaration.inc', [5]);
    }

    /**
     * Drive the directory sniff, which this concern has no sniff file of its
     * own to stand in for.
     *
     * @return string
     */
    #[\Override]
    protected function sniffFile(): string
    {
        return dirname(__DIR__, 2) . '/Sniffs/Structure/RequireRoleDirectorySniff.php';
    }

    /**
     * Apply the per-test property overrides.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function sniffProperties(): array
    {
        return $this->overrides;
    }
}
