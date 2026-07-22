<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Debug;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMaculaLaravel\Sniffs\Debug\DisallowDebugStatementsSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the debug statements sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowDebugStatementsSniff::class)]
#[CoversTrait(DetectsFunctionCalls::class)]
final class DisallowDebugStatementsSniffTest extends AbstractSniffTestCase
{
    /**
     * Debug helper calls are flagged in any letter case; method/static calls,
     * declarations, other function calls and a bare constant fetch of the same
     * name are not.
     *
     * @return void
     */
    public function testFlagsDebugStatements(): void
    {
        $this->assertErrorsOnLines('DisallowDebugStatements.inc', [11, 12, 13, 14, 15, 21]);
    }

    /**
     * The error names the debug helper exactly as written.
     *
     * @return void
     */
    public function testReportsDebugHelperNameInMessage(): void
    {
        $this->assertErrorMessagesOnLines('DisallowDebugStatements.inc', [
            11 => ['Debug statement "dd()" must not be committed; remove it.'],
            12 => ['Debug statement "dump()" must not be committed; remove it.'],
            13 => ['Debug statement "ray()" must not be committed; remove it.'],
            14 => ['Debug statement "var_dump()" must not be committed; remove it.'],
            15 => ['Debug statement "print_r()" must not be committed; remove it.'],
            21 => ['Debug statement "DD()" must not be committed; remove it.'],
        ]);
    }
}
