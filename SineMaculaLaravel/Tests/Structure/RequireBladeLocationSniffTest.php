<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Structure\RequireBladeLocationSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the Blade template location sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(RequireBladeLocationSniff::class)]
final class RequireBladeLocationSniffTest extends AbstractSniffTestCase
{
    /**
     * A Blade template outside a views directory is flagged.
     *
     * @return void
     */
    public function testFlagsBladeOutsideViews(): void
    {
        $this->assertErrorsOnLines('template.blade.inc', [1]);
    }

    /**
     * A Blade template under resources/views is not flagged.
     *
     * @return void
     */
    public function testAllowsBladeUnderViews(): void
    {
        $this->assertErrorsOnLines('resources/views/template.blade.inc', []);
    }

    /**
     * A file that is not a Blade template is ignored wherever it sits.
     *
     * @return void
     */
    public function testIgnoresNonBladeFiles(): void
    {
        $this->assertErrorsOnLines('NotBlade.inc', []);
    }

    /**
     * The fixtures use the `.blade.inc` extension, so point the sniff at it.
     *
     * @return array<string, mixed>
     */
    #[\Override]
    protected function sniffProperties(): array
    {
        return ['extension' => '.blade.inc'];
    }
}
