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
     * getXAttribute/setXAttribute methods are flagged; getAttribute and other
     * methods are not.
     *
     * @return void
     */
    public function testFlagsLegacyAccessors(): void
    {
        $this->assertErrorsOnLines('DisallowLegacyAttributeAccessor.inc', [7, 12]);
    }
}
