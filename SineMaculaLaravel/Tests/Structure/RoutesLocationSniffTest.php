<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversNothing;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the routes file location sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversNothing]
final class RoutesLocationSniffTest extends AbstractSniffTestCase
{
    /**
     * A routes file outside an Http directory is flagged.
     *
     * @return void
     */
    public function testFlagsRoutesOutsideHttp(): void
    {
        $this->assertErrorsOnLines('routes.inc', [1]);
    }

    /**
     * A routes file directly inside an Http directory is not flagged.
     *
     * @return void
     */
    public function testAllowsRoutesAtHttpRoot(): void
    {
        $this->assertErrorsOnLines('Http/routes.inc', []);
    }

    /**
     * A file that is not the routes file is ignored wherever it sits.
     *
     * @return void
     */
    public function testIgnoresNonRoutesFiles(): void
    {
        $this->assertErrorsOnLines('NotRoutes.inc', []);
    }

    /**
     * The fixtures use the `.inc` extension, so point the sniff at that name.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function sniffProperties(): array
    {
        return ['filename' => 'routes.inc'];
    }
}
