<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\TypeHints;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SineMaculaLaravel\Sniffs\TypeHints\ParameterTypeHintSniff;
use SineMaculaLaravel\Sniffs\TypeHints\PropertyTypeHintSniff;
use SineMaculaLaravel\Sniffs\TypeHints\ReturnTypeHintSniff;

/**
 * Tests that the SineMaculaLaravel standard swaps the Slevomat native-type
 * requirement for the Laravel-aware sniffs.
 *
 * Building the real standard proves the ruleset wiring: the Laravel sniff fires
 * on an untyped property, while the excluded Slevomat native/any codes never
 * fire (so nothing is reported twice).
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(PropertyTypeHintSniff::class)]
#[CoversClass(ParameterTypeHintSniff::class)]
#[CoversClass(ReturnTypeHintSniff::class)]
final class TypeHintsRulesetTest extends TestCase
{
    /** @var array<int, string> The Slevomat native/any codes the standard drops. */
    private const array EXCLUDED_SLEVOMAT = [
        'SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint',
        'SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint',
        'SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint',
        'SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint',
        'SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint',
        'SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingAnyTypeHint',
    ];

    /**
     * The Laravel property sniff fires under the full standard, and none of the
     * excluded Slevomat native/any type-hint codes are reported.
     *
     * @return void
     */
    public function testStandardReplacesSlevomatNativeTypeHints(): void
    {
        $sources = $this->typeHintSources();

        self::assertContains('SineMaculaLaravel.TypeHints.PropertyTypeHint.MissingNativeTypeHint', $sources);
        self::assertSame([], array_values(array_intersect(self::EXCLUDED_SLEVOMAT, $sources)));
    }

    /**
     * Run the SineMaculaLaravel standard over the fixture and collect the
     * sources of every reported type-hint error.
     *
     * @return array<int, string>
     */
    private function typeHintSources(): array
    {
        $config  = new Config(['--standard=SineMaculaLaravel', '--extensions=inc,php'], false);
        $ruleset = new Ruleset($config);
        $file    = new LocalFile(__DIR__ . DIRECTORY_SEPARATOR . 'TypeHintsRuleset.inc', $ruleset, $config);

        $file->process();

        return array_values(array_filter(
            $this->sources($file),
            static fn (string $source): bool => str_contains($source, '.TypeHints.'),
        ));
    }

    /**
     * Collect the sniff source code of every reported error.
     *
     * @param  \PHP_CodeSniffer\Files\LocalFile  $file
     * @return array<int, string>
     */
    private function sources(LocalFile $file): array
    {
        $sources = [];

        foreach ($file->getErrors() as $columns) {
            foreach ($columns as $messages) {
                foreach ($messages as $message) {
                    $sources[] = $message['source'];
                }
            }
        }

        return $sources;
    }
}
