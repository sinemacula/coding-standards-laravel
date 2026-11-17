<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Controllers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\IdentifiesControllers;
use SineMaculaLaravel\Sniffs\Controllers\DisallowDatabaseAccessSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the controller database-access sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowDatabaseAccessSniff::class)]
#[CoversTrait(IdentifiesControllers::class)]
final class DisallowDatabaseAccessSniffTest extends AbstractSniffTestCase
{
    /**
     * Flags DB facade calls and static calls on imported models in controllers.
     *
     * @return void
     */
    public function testFlagsDatabaseAccessInControllers(): void
    {
        $this->assertErrorsOnLines('DisallowDatabaseAccess.inc', [13, 14, 15]);
    }

    /**
     * Renders the facade and Eloquent errors verbatim.
     *
     * @return void
     */
    public function testRendersExactErrorMessages(): void
    {
        $this->assertErrorMessagesOnLines('DisallowDatabaseAccess.inc', [
            13 => ['Controllers must not query the database directly via the DB facade; use a repository.'],
            14 => ['Controllers must not query Eloquent models directly ("User::"); use a repository.'],
            15 => ['Controllers must not query Eloquent models directly ("User::"); use a repository.'],
        ]);
    }

    /**
     * Resolves model imports and controller scope across edge cases.
     *
     * @return void
     */
    public function testResolvesImportsAndScopesAcrossEdgeCases(): void
    {
        $this->assertErrorsOnLines('DisallowDatabaseAccessImports.inc', [9, 10, 22]);
    }
}
