<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Eloquent;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDeclarationNames;
use SineMaculaLaravel\Sniffs\Eloquent\DisallowLegacyAttributeAccessorSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the legacy attribute accessor sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowLegacyAttributeAccessorSniff::class)]
#[CoversTrait(ReadsDeclarationNames::class)]
final class DisallowLegacyAttributeAccessorSniffTest extends AbstractSniffTestCase
{
    /**
     * Flags only get/set XAttribute methods with accessor arity on a model.
     *
     * @return void
     */
    public function testFlagsOnlyGenuineModelAccessors(): void
    {
        $this->assertErrorsOnLines('DisallowLegacyAttributeAccessor.inc', [9, 18, 69]);
    }

    /**
     * Names the offending method and points at Attribute::make().
     *
     * @return void
     */
    public function testReportsTheOffendingMethodName(): void
    {
        $this->assertErrorMessagesOnLines('DisallowLegacyAttributeAccessor.inc', [
            9  => ['Legacy accessor/mutator "getNameAttribute()" is not allowed; define the attribute via Attribute::make().'],
            18 => ['Legacy accessor/mutator "setNameAttribute()" is not allowed; define the attribute via Attribute::make().'],
            69 => ['Legacy accessor/mutator "getLabelAttribute()" is not allowed; define the attribute via Attribute::make().'],
        ]);
    }

    /**
     * Matches the accessor pattern against the whole method name only.
     *
     * @return void
     */
    public function testMatchesTheWholeMethodNameOnly(): void
    {
        $this->assertErrorsOnLines('DisallowLegacyAttributeAccessorNameBoundaries.inc', [22]);
    }

    /**
     * Judges a class nested in a method by its own parent.
     *
     * @return void
     */
    public function testUsesTheNearestEnclosingClass(): void
    {
        $this->assertErrorsOnLines('DisallowLegacyAttributeAccessorNestedClass.inc', [13]);
    }

    /**
     * A namespace-qualified parent is matched on its short name.
     *
     * @return void
     */
    public function testMatchesQualifiedParentOnShortName(): void
    {
        $this->assertErrorsOnLines('DisallowLegacyAttributeAccessorQualifiedParent.inc', [7]);
    }
}
