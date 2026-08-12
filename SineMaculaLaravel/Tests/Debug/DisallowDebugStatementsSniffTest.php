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
    /** @var string The expected error message. */
    private const string PRINT_R_ERROR = 'Debug statement "print_r()" must not be committed; remove it.';

    /**
     * Debug helper calls are flagged in any letter case; method/static calls,
     * declarations, other function calls and a bare constant fetch of the same
     * name are not. A print_r whose second argument returns the output prints
     * nothing and is left alone, in any letter case; one that still prints is
     * flagged, including where a nested array puts a comma inside the only
     * argument. A helper with no return form, such as dd, is flagged whatever
     * its second argument.
     *
     * @return void
     */
    public function testFlagsDebugStatements(): void
    {
        $this->assertErrorsOnLines('DisallowDebugStatements.inc', [11, 12, 13, 14, 15, 21, 34, 35, 36, 48, 49]);
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
            15 => [self::PRINT_R_ERROR],
            21 => ['Debug statement "DD()" must not be committed; remove it.'],
            34 => [self::PRINT_R_ERROR],
            35 => [self::PRINT_R_ERROR],
            36 => [self::PRINT_R_ERROR],
            48 => ['Debug statement "dd()" must not be committed; remove it.'],
            49 => [self::PRINT_R_ERROR],
        ]);
    }
}
