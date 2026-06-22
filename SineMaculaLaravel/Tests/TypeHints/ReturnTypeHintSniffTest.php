<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\TypeHints;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\TypeHints\ReturnTypeHintSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the Laravel-aware return type-hint sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(ReturnTypeHintSniff::class)]
final class ReturnTypeHintSniffTest extends AbstractSniffTestCase
{
    /**
     * A function, method or closure without a native return type is flagged; a
     * typed method, a constructor and a method carrying #[\Override] are not.
     *
     * @return void
     */
    public function testFlagsMissingReturnTypesExceptConstructorsAndOverrides(): void
    {
        $this->assertErrorsOnLines('ReturnTypeHint.inc', [7, 26, 30]);
    }
}
