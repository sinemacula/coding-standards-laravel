<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Architecture;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Architecture\DisallowServiceLocationSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the service location sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowServiceLocationSniff::class)]
final class DisallowServiceLocationSniffTest extends AbstractSniffTestCase
{
    /**
     * Container helpers and the App::make facade are flagged inside a class;
     * injected dependencies and helpers outside a class are not.
     *
     * @return void
     */
    public function testFlagsServiceLocationInClassBodies(): void
    {
        $this->assertErrorsOnLines('DisallowServiceLocation.inc', [11, 16, 21]);
    }
}
