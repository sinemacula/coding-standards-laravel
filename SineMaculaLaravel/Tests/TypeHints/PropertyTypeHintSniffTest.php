<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\TypeHints;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDocblockTags;
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
#[CoversTrait(ReadsDocblockTags::class)]
final class PropertyTypeHintSniffTest extends AbstractSniffTestCase
{
    /**
     * Flags untyped class properties other than framework-magic ones.
     *
     * @return void
     */
    public function testFlagsUntypedNonMagicProperties(): void
    {
        $this->assertErrorsOnLines('PropertyTypeHint.inc', [16]);
    }

    /**
     * No property in the framework-magic exempt set is flagged.
     *
     * @return void
     */
    public function testExemptsEveryFrameworkMagicProperty(): void
    {
        $this->assertErrorsOnLines('PropertyTypeHintMagicProperties.inc', []);
    }

    /**
     * Exempts a property whose own docblock is tagged @untypeable.
     *
     * @return void
     */
    public function testExemptsPropertiesMarkedUntypeable(): void
    {
        $this->assertErrorsOnLines('PropertyTypeHintDocblocks.inc', [31, 36, 38, 47]);
    }

    /**
     * The error names the offending property and points at the escape hatch.
     *
     * @return void
     */
    public function testRendersPropertyNameInErrorMessage(): void
    {
        $this->assertErrorMessagesOnLines('PropertyTypeHint.inc', [
            16 => [
                'Property $nickname must have a native type hint, or be marked @untypeable '
                . 'if it overrides an untyped parent declaration that PHP forbids typing.',
            ],
        ]);
    }
}
