<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\TypeHints;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\TypeHints\PropertyTypeHintSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the Laravel-aware property type-hint sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(PropertyTypeHintSniff::class)]
final class PropertyTypeHintSniffTest extends AbstractSniffTestCase
{
    /**
     * An untyped class property is flagged; framework-magic properties (incl.
     * $dateFormat), a typed property, parameters, locals and top-level vars are
     * not.
     *
     * @return void
     */
    public function testFlagsUntypedNonMagicProperties(): void
    {
        $this->assertErrorsOnLines('PropertyTypeHint.inc', [15]);
    }
}
