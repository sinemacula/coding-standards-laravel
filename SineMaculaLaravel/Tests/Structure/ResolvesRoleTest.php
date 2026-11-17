<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsRelatedNames;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesRole;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for role resolution.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversTrait(ReadsRelatedNames::class)]
#[CoversTrait(ResolvesRole::class)]
final class ResolvesRoleTest extends AbstractSniffTestCase
{
    /** @var string The error for a job outside a jobs directory. */
    private const string JOB_MISPLACED = 'A Job class must live under a "Jobs" directory.';

    /** @var array<string, mixed> Per-test property overrides for the sniff. */
    private array $overrides = [];

    /**
     * An attribute naming a model does not make the attributed class a model.
     *
     * @return void
     */
    public function testLeavesAClassAttributedWithAForeignModel(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryForeignAttribute.inc', [15]);
    }

    /**
     * A declared identity outranks an attribute, whatever the table order.
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
     * Resolves the Job identity through every import shape and nothing else.
     *
     * @return void
     */
    public function testResolvesJobIdentityThroughImports(): void
    {
        $this->assertErrorsOnLines('RoleDirectorySyncJob.inc', [15, 20, 25, 30, 35]);
    }

    /**
     * Matches a bare identity against a fully qualified parent's last segment.
     *
     * @return void
     */
    public function testMatchesABareIdentityAgainstAQualifiedDeclaration(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryQualifiedDeclaration.inc', [5]);
    }

    /**
     * A parent class outranks an interface beside it.
     *
     * @return void
     */
    public function testPrefersTheParentOverASharedInterface(): void
    {
        $this->assertErrorMessagesOnLines('RoleDirectoryQueuedParent.inc', [
            9  => ['A Notification class must live under a "Notifications" directory.'],
            13 => ['A Mailable class must live under a "Mail" directory.'],
            17 => [self::JOB_MISPLACED],
        ]);
    }

    /**
     * An implemented interface outranks an attribute, as the parent does.
     *
     * @return void
     */
    public function testPrefersAnInterfaceOverAnAttribute(): void
    {
        $this->overrides = ['roleIdentities' => ['Command' => 'AsCommand', 'Job' => 'ShouldQueue']];

        $this->assertErrorMessagesOnLines('RoleDirectoryContractBeatsAttribute.inc', [
            8 => [self::JOB_MISPLACED],
        ]);
    }

    /**
     * Location tells a queued listener from a job sharing its identity.
     *
     * @return void
     */
    public function testTellsAQueuedListenerFromAJobByLocation(): void
    {
        $this->assertErrorsOnLines('RoleDirectoryQueuedListener.inc', []);
        $this->assertErrorMessagesOnLines('RoleDirectoryQueuedListenerConcern.inc', [
            7 => [self::JOB_MISPLACED],
        ]);
    }

    /**
     * Matches a qualified shared identity through an aliased import.
     *
     * @return void
     */
    public function testMatchesAQualifiedSharedIdentityThroughAnAlias(): void
    {
        $this->overrides = [
            'roleIdentities'   => ['Job' => 'Illuminate\Contracts\Queue\ShouldQueue'],
            'sharedIdentities' => ['Illuminate\Contracts\Queue\ShouldQueue' => 'Listener'],
        ];

        $this->assertErrorsOnLines('RoleDirectoryQueuedListenerAliased.inc', []);
        $this->assertErrorMessagesOnLines('RoleDirectoryQueuedJobAliased.inc', [7 => [self::JOB_MISPLACED]]);
    }

    /**
     * A distinct job identity stays a job under a listener directory.
     *
     * @return void
     */
    public function testKeepsADistinctJobIdentityUnderAListenerDirectory(): void
    {
        $this->assertErrorMessagesOnLines('RoleDirectoryListenerJob.inc', [
            8  => [self::JOB_MISPLACED],
            13 => [self::JOB_MISPLACED],
        ]);
    }

    /**
     * Drive the directory sniff, which names the resolved role in its message.
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
