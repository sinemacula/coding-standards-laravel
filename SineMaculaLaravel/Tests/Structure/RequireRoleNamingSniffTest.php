<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Structure\RequireRoleNamingSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the role naming sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(RequireRoleNamingSniff::class)]
final class RequireRoleNamingSniffTest extends AbstractSniffTestCase
{
    /**
     * A class under a role directory without the role suffix is flagged; one
     * that carries the suffix is not.
     *
     * @return void
     */
    public function testFlagsMisnamedRoleClasses(): void
    {
        $this->assertErrorsOnLines('RequireRoleNamingMatched.inc', [9]);
    }

    /**
     * A class outside any role directory is not flagged.
     *
     * @return void
     */
    public function testIgnoresClassesOutsideRoleDirectories(): void
    {
        $this->assertErrorsOnLines('RequireRoleNamingOutside.inc', []);
    }
}
