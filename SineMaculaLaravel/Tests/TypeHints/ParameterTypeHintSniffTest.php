<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\TypeHints;

use PHPUnit\Framework\Attributes\CoversClass;
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
final class ParameterTypeHintSniffTest extends AbstractSniffTestCase
{
    /**
     * An untyped parameter is flagged; a typed parameter and every parameter of
     * a method carrying #[\Override] are not.
     *
     * @return void
     */
    public function testFlagsUntypedParametersExceptOnOverrides(): void
    {
        $this->assertErrorsOnLines('ParameterTypeHint.inc', [7, 18]);
    }
}
