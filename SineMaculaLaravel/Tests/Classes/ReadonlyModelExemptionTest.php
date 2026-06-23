<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Classes;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the SineMaculaLaravel standard exempts Eloquent models from the
 * readonly-public-property requirement.
 *
 * Building the real standard proves the ruleset wiring: it sets the base
 * RequireReadonlyPublicProperty sniff's ignoredParentClasses to the model
 * bases, so a model's public magic properties are not flagged while an
 * ordinary class still is.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversNothing]
final class ReadonlyModelExemptionTest extends TestCase
{
    /**
     * A public property on a Model, Authenticatable or Pivot subclass is not
     * flagged as needing readonly; one on an ordinary class still is.
     *
     * @return void
     */
    public function testExemptsModelMagicPropertiesFromReadonly(): void
    {
        self::assertSame([28], $this->readonlyViolationLines());
    }

    /**
     * Run the SineMaculaLaravel standard over the fixture and collect the lines
     * flagged by the readonly-public-property sniff.
     *
     * @return array<int, int>
     */
    private function readonlyViolationLines(): array
    {
        $config  = new Config(['--standard=SineMaculaLaravel', '--extensions=inc,php'], false);
        $ruleset = new Ruleset($config);
        $file    = new LocalFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReadonlyModelExemption.inc', $ruleset, $config);

        $file->process();

        $lines = array_map(
            static fn (array $violation): int => $violation['line'],
            array_filter(
                $this->violations($file),
                static fn (array $violation): bool => str_contains($violation['source'], 'RequireReadonlyPublicProperty'),
            ),
        );

        sort($lines);

        return array_values(array_unique($lines));
    }

    /**
     * Flatten every reported error into its line and sniff source.
     *
     * @param  \PHP_CodeSniffer\Files\LocalFile  $file
     * @return array<int, array{line: int, source: string}>
     */
    private function violations(LocalFile $file): array
    {
        $violations = [];

        foreach ($file->getErrors() as $line => $columns) {
            foreach ($columns as $messages) {
                foreach ($messages as $message) {
                    $violations[] = ['line' => $line, 'source' => $message['source']];
                }
            }
        }

        return $violations;
    }
}
