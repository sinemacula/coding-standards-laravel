<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Eloquent;

use PHPUnit\Framework\Attributes\CoversClass;
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
final class DisallowLegacyAttributeAccessorSniffTest extends AbstractSniffTestCase
{
    /**
     * Only genuine accessors are flagged: a get/set XAttribute with the right
     * arity on a Model. A signature mismatch, a non-model class (even one whose
     * method shares the name), and a non-accessor name are all clean.
     *
     * @return void
     */
    public function testFlagsOnlyGenuineModelAccessors(): void
    {
        $this->assertErrorsOnLines('DisallowLegacyAttributeAccessor.inc', [9, 18]);
    }

    /**
     * The reported error names the offending method and points at the
     * Attribute::make() replacement.
     *
     * @return void
     */
    public function testReportsTheOffendingMethodName(): void
    {
        $this->assertErrorMessagesOnLines('DisallowLegacyAttributeAccessor.inc', [
            9  => ['Legacy accessor/mutator "getNameAttribute()" is not allowed; define the attribute via Attribute::make().'],
            18 => ['Legacy accessor/mutator "setNameAttribute()" is not allowed; define the attribute via Attribute::make().'],
        ]);
    }

    /**
     * The accessor pattern covers the whole method name: a prefixed or suffixed
     * near-miss is clean, as is a non-accessor name of any arity.
     *
     * @return void
     */
    public function testMatchesTheWholeMethodNameOnly(): void
    {
        $this->assertErrorsOnLines('DisallowLegacyAttributeAccessorNameBoundaries.inc', [22]);
    }

    /**
     * A class declared inside another class's method is judged by its own
     * parent, so a model nested in a non-model method is still flagged.
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
