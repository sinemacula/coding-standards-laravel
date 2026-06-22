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
}
