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
     * An untyped class property is flagged; framework-magic properties (incl.
     * $dateFormat and a factory's $model), a typed property, parameters, locals
     * and top-level vars are not.
     *
     * @return void
     */
    public function testFlagsUntypedNonMagicProperties(): void
    {
        $this->assertErrorsOnLines('PropertyTypeHint.inc', [16]);
    }

    /**
     * Every name in the exempt set covers a framework base class that declares
     * the property untyped, so none of them is flagged.
     *
     * @return void
     */
    public function testExemptsEveryFrameworkMagicProperty(): void
    {
        $this->assertErrorsOnLines('PropertyTypeHintMagicProperties.inc', []);
    }

    /**
     * @untypeable on the property's own docblock is the escape hatch for base
     * classes the exempt set cannot know about; an unrelated tag, a bare
     * mention in prose and a preceding property's docblock do not exempt.
     *
     * @return void
     */
    public function testExemptsPropertiesMarkedUntypeable(): void
    {
        $this->assertErrorsOnLines('PropertyTypeHintDocblocks.inc', [31, 36, 38]);
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
