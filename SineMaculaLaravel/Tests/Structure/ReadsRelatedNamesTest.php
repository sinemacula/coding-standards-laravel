<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsRelatedNames;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for reading the names a class relates to.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversTrait(ReadsRelatedNames::class)]
final class ReadsRelatedNamesTest extends AbstractSniffTestCase
{
    /** @var string The error for a job outside a jobs directory. */
    private const string JOB_MISPLACED = 'A Job class must live under a "Jobs" directory.';

    /**
     * Reads every trait the class body uses, but not conflict or closure names.
     *
     * @return void
     */
    public function testReadsEveryTraitTheClassBodyUses(): void
    {
        $this->assertErrorMessagesOnLines('RelatedNamesTraits.inc', [
            5  => [self::JOB_MISPLACED],
            11 => [self::JOB_MISPLACED],
        ]);
    }

    /**
     * Reads every attribute on the class, but not names passed as arguments.
     *
     * @return void
     */
    public function testReadsEveryAttributeOnTheClass(): void
    {
        $this->assertErrorMessagesOnLines('RelatedNamesAttributes.inc', [
            7  => [self::JOB_MISPLACED],
            13 => [self::JOB_MISPLACED],
            17 => [self::JOB_MISPLACED],
            27 => [self::JOB_MISPLACED],
        ]);
    }

    /**
     * Drive the role directory sniff, which names the resolved role.
     *
     * @return string
     */
    #[\Override]
    protected function sniffFile(): string
    {
        return dirname(__DIR__, 2) . '/Sniffs/Structure/RequireRoleDirectorySniff.php';
    }

    /**
     * Make `Marker` the only role identity, so the names read decide the role.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function sniffProperties(): array
    {
        return ['roleIdentities' => ['Job' => 'Marker']];
    }
}
