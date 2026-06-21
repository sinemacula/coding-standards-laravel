<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Services;

use PHPUnit\Framework\Attributes\CoversNothing;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the HTTP-abort-in-services sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversNothing]
final class DisallowHttpAbortSniffTest extends AbstractSniffTestCase
{
    /**
     * abort() helpers and *HttpException instantiations are flagged inside a
     * Services namespace; domain exceptions are not.
     *
     * @return void
     */
    public function testFlagsHttpAbortsInServices(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbort.inc', [9, 10, 11, 13]);
    }

    /**
     * The same calls are ignored outside a Services namespace.
     *
     * @return void
     */
    public function testIgnoresNonServiceNamespaces(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortNonService.inc', []);
    }

    /**
     * The same calls are ignored in a file with no namespace.
     *
     * @return void
     */
    public function testIgnoresFilesWithoutANamespace(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortGlobal.inc', []);
    }
}
