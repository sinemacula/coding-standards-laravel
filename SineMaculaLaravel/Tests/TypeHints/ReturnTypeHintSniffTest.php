<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\TypeHints;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsAttributes;
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
#[CoversTrait(ReadsAttributes::class)]
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

    /**
     * The error names the offending declaration: a method or function by name
     * with parentheses, a closure as "Closure".
     *
     * @return void
     */
    public function testRendersDeclarationNameInErrorMessage(): void
    {
        $this->assertErrorMessagesOnLines('ReturnTypeHint.inc', [
            7  => ['compute() must declare a native return type hint.'],
            26 => ['helper() must declare a native return type hint.'],
            30 => ['Closure must declare a native return type hint.'],
        ]);
    }

    /**
     * #[\Override] is recognised in every written form: unqualified, stacked
     * across groups (adjacent or not), grouped after another attribute with
     * arguments, flush against the declaration, and on a closure inside a call.
     * An Override name inside another attribute's arguments does not count.
     *
     * @return void
     */
    public function testReadsOverrideAcrossAttributeForms(): void
    {
        $this->assertErrorsOnLines('ReturnTypeHintAttributes.inc', [29]);
    }
}
