<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Controllers;

use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDocblockTags;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for reading a declaration's docblock tags, rather than for any sniff
 * that offers them as an escape hatch. The non-REST action sniff is driven
 * because it reads tags on both classes and methods.
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
     * Docblock tags are matched case-insensitively and only from the docblock
     * attached to the declaration: a bare method after a tagged one and a
     * method whose docblock lacks the tags are flagged, while a mixed-case
     * utility tag and a docblock glued to the function keyword still exempt.
     *
     * @return void
     */
    public function testReadsDocblockTagsFromTheAttachedDocblockOnly(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsDocblocks.inc', [14, 23]);
    }

    /**
     * Attributes between a docblock and its declaration do not detach the
     * docblock: a single attribute, stacked, grouped, same-line and glued
     * attributes all keep the tags readable on methods and classes, while an
     * attributed method with no docblock or an untagged one is still flagged.
     *
     * @return void
     */
    public function testReadsDocblockTagsPastAttributes(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsAttributes.inc', [49, 57]);
    }

    /**
     * The tag exemption reads only the docblock attached to the method: a bare
     * method is flagged even when an earlier method's docblock carries the same
     * tag.
     *
     * @return void
     */
    public function testScopesTheTagExemptionToItsOwnMethod(): void
    {
        $this->assertErrorsOnLines('DisallowNonRestActionsEarlierTag.inc', [16]);
    }

    /**
     * Drive the non-REST action sniff, which this concern has no sniff file of
     * its own to stand in for.
     *
     * @return string
     */
    #[\Override]
    protected function sniffFile(): string
    {
        return dirname(__DIR__, 2) . '/Sniffs/Controllers/DisallowNonRestActionsSniff.php';
    }
}
