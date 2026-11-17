<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Controllers;

use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDocblockTags;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for reading a declaration's docblock tags.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversTrait(ReadsDocblockTags::class)]
final class ReadsDocblockTagsTest extends AbstractSniffTestCase
{
    /**
     * Matches tags case-insensitively from the attached docblock only.
     *
     * @return void
     */
    public function testReadsDocblockTagsFromTheAttachedDocblockOnly(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsDocblocks.inc', [14, 23]);
    }

    /**
     * Attributes between a docblock and its declaration do not detach it.
     *
     * @return void
     */
    public function testReadsDocblockTagsPastAttributes(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsAttributes.inc', [49, 57]);
    }

    /**
     * An earlier method's tag does not exempt a later bare method.
     *
     * @return void
     */
    public function testScopesTheTagExemptionToItsOwnMethod(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsEarlierTag.inc', [16]);
    }

    /**
     * Drive the non-REST action sniff, which reads tags on classes and methods.
     *
     * @return string
     */
    #[\Override]
    protected function sniffFile(): string
    {
        return dirname(__DIR__, 2) . '/Sniffs/Controllers/DisallowNonRestActionsSniff.php';
    }
}
