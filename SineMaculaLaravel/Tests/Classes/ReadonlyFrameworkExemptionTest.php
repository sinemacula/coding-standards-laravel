<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Classes;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the standard exempts framework bases declaring public magic
 * properties from the readonly-public-property requirement.
 *
 * Builds the real standard to prove its ignoredParentClasses wiring.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversNothing]
final class ReadonlyFrameworkExemptionTest extends TestCase
{
    /**
     * Flags an ordinary class's public property but not a framework base's.
     *
     * @return void
     */
    public function testExemptsFrameworkMagicPropertiesFromReadonly(): void
    {
        self::assertSame([32], $this->readonlyViolationLines());
    }

    /**
     * Collect the lines the readonly-public-property sniff flags.
     *
     * @return array<int, int>
     */
    private function readonlyViolationLines(): array
    {
        $config  = new Config(['--standard=SineMaculaLaravel', '--extensions=inc,php'], false);
        $ruleset = new Ruleset($config);
        $file    = new LocalFile(__DIR__ . DIRECTORY_SEPARATOR . 'ReadonlyFrameworkExemption.inc', $ruleset, $config);

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
