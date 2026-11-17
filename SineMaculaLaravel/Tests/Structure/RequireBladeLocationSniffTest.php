<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * Templates outside a views directory.
     *
     * @return iterable<string, array{string}>
     */
    public static function misplacedFixtures(): iterable
    {
        yield 'outside resources' => ['template.blade.inc'];
        yield 'opening with a PHP tag' => ['script.blade.inc'];
        yield 'path merely ending in a views path' => ['resources/notresources/views/template.blade.inc'];
        yield 'directory merely prefixed with views' => ['resources/viewsmore/template.blade.inc'];
    }

    /**
     * Flags a Blade template outside a views directory.
     *
     * @param  string  $fixture
     * @return void
     */
    #[DataProvider('misplacedFixtures')]
    public function testFlagsBladeOutsideViews(string $fixture): void
    {
        $this->assertErrorsOnLines($fixture, [1]);
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
