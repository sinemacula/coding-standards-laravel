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
     * A Blade template that opens with a PHP tag is still flagged.
     *
     * @return void
     */
    public function testFlagsBladeStartingWithPhpTag(): void
    {
        $this->assertErrorsOnLines('script.blade.inc', [1]);
    }

    /**
     * A Blade template under a directory whose path merely ends in a views
     * path is flagged.
     *
     * @return void
     */
    public function testFlagsBladeUnderSuffixedViewsPath(): void
    {
        $this->assertErrorsOnLines('resources/notresources/views/template.blade.inc', [1]);
    }

    /**
     * A Blade template under a directory whose name merely starts with views
     * is flagged.
     *
     * @return void
     */
    public function testFlagsBladeUnderPrefixedViewsDirectory(): void
    {
        $this->assertErrorsOnLines('resources/viewsmore/template.blade.inc', [1]);
    }

    /**
     * The misplaced error names the first allowed directory.
     *
     * @return void
     */
    public function testReportsFirstAllowedDirectoryInMessage(): void
    {
        $this->assertErrorMessagesOnLines('template.blade.inc', [
            1 => ['A Blade template must live under a "resources/views" directory.'],
        ]);
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
