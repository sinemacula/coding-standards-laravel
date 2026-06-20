<?php

namespace SineMaculaLaravel\Tests\Architecture;

use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the service location sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
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
