<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Structure\RequireRoleDirectorySniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the role directory placement sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(RequireRoleDirectorySniff::class)]
final class RequireRoleDirectorySniffTest extends AbstractSniffTestCase
{
    /**
     * A role class (by suffix) outside its directory is flagged; a class with
     * no recognised role is not.
     *
     * @return void
     */
    public function testFlagsMisplacedRoleClasses(): void
    {
        $this->assertErrorsOnLines('RequireRoleDirectoryMisplaced.inc', [5]);
    }

    /**
     * A role class under its canonical directory is not flagged.
     *
     * @return void
     */
    public function testAllowsCorrectlyPlacedClasses(): void
    {
        $this->assertErrorsOnLines('RequireRoleDirectoryPlaced.inc', []);
    }

    /**
     * A role class with no namespace is flagged (it is not in its directory).
     *
     * @return void
     */
    public function testFlagsClassesWithoutANamespace(): void
    {
        $this->assertErrorsOnLines('RequireRoleDirectoryGlobal.inc', [3]);
    }
}
