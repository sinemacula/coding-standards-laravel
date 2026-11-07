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
    /** @var string The error for a job outside a jobs directory. */
    private const string JOB_MISPLACED = 'A Job class must live under a "Jobs" directory.';

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
     * A parent class outranks an interface beside it: a queued notification or
     * mailable keeps its own role rather than reading as a job, while a class
     * that is only queued is still a job.
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
     * A queued listener shares the job's identity, so its location decides:
     * under a listener directory it is a listener, while one in an exempt
     * sub-namespace there is not placed by that location and stays a job.
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
     * A qualified shared identity is matched through the imports like a role
     * identity, even when it names the interface in full: imported under an
     * alias, it makes a queued listener a listener and any other queued class a
     * job.
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
     * Location only decides between roles sharing an identity: a class with a
     * job's distinct identity under a listener directory, queued or not, is
     * still a job.
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
