<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\TypeHints;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsAttributes;
use SineMaculaLaravel\Sniffs\TypeHints\ParameterTypeHintSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the Laravel-aware parameter type-hint sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(ParameterTypeHintSniff::class)]
#[CoversTrait(ReadsAttributes::class)]
final class ParameterTypeHintSniffTest extends AbstractSniffTestCase
{
    /**
     * An untyped parameter is flagged; a typed one, those on an #[\Override]
     * method, and those on a non-private trait method are not. A private trait
     * method's parameters still are, as is an untyped parameter that follows a
     * typed one.
     *
     * @return void
     */
    public function testFlagsUntypedParametersExceptOnOverrides(): void
    {
        $this->assertErrorsOnLines('ParameterTypeHint.inc', [7, 18, 34, 42]);
    }

    /**
     * The error names the offending parameter.
     *
     * @return void
     */
    public function testRendersParameterNameInErrorMessage(): void
    {
        $this->assertErrorMessagesOnLines('ParameterTypeHint.inc', [
            7  => ['Parameter $payload must have a native type hint.'],
            18 => ['Parameter $context must have a native type hint.'],
            34 => ['Parameter $value must have a native type hint.'],
            42 => ['Parameter $second must have a native type hint.'],
        ]);
    }
}
