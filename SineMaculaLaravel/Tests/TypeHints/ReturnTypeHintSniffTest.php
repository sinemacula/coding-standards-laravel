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
     * Flags missing return types except on constructors and overrides.
     *
     * @return void
     */
    public function testFlagsMissingReturnTypesExceptConstructorsAndOverrides(): void
    {
        $this->assertErrorsOnLines('ReturnTypeHint.inc', [7, 26, 30]);
    }

    /**
     * Names a function or method with parentheses and a closure as "Closure".
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
     * Recognises #[\Override] in every attribute form, but not as an argument.
     *
     * @return void
     */
    public function testReadsOverrideAcrossAttributeForms(): void
    {
        $this->assertErrorsOnLines('ReturnTypeHintAttributes.inc', [29]);
    }
}
