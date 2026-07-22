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
     * DB:: facade calls and static calls on imported models are flagged in a
     * controller; other facades, variable/instance calls, constants and
     * non-controllers are not.
     *
     * @return void
     */
    public function testFlagsDatabaseAccessInControllers(): void
    {
        $this->assertErrorsOnLines('DisallowDatabaseAccess.inc', [13, 14, 15]);
    }

    /**
     * The facade error and the Eloquent error (with the model name filled in)
     * are rendered exactly as written.
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
     * Model imports are collected from an import on the open-tag line, after a
     * non-model import and from an import glued to the previous semicolon;
     * static:: and instance calls are never treated as class calls, and a
     * controller declared inside a function is still recognised.
     *
     * @return void
     */
    public function testResolvesImportsAndScopesAcrossEdgeCases(): void
    {
        $this->assertErrorsOnLines('DisallowDatabaseAccessImports.inc', [9, 10, 22]);
    }
}
