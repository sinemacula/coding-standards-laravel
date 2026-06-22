<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Structure;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SineMaculaLaravel\Sniffs\Structure\RequireRoleDirectorySniff;
use SineMaculaLaravel\Sniffs\Structure\RequireRoleNamingSniff;

/**
 * Tests that the package standard composes the app standard without its
 * directory-skeleton rules.
 *
 * A misnamed, misplaced controller is flagged by the app standard but yields
 * zero Structure/Controllers violations under the package standard, so a
 * domain-organised library never inherits app layout rules.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(RequireRoleNamingSniff::class)]
#[CoversClass(RequireRoleDirectorySniff::class)]
final class PackageRulesetTest extends TestCase
{
    /**
     * The app standard flags the controller's structure; the package standard
     * excludes those sniffs, so it reports none.
     *
     * @return void
     */
    public function testPackageStandardExcludesAppStructureRules(): void
    {
        self::assertNotSame([], $this->structureViolations('SineMaculaLaravel'));
        self::assertSame([], $this->structureViolations('SineMaculaLaravelPackage'));
    }

    /**
     * Run a standard over the fixture and collect its Structure/Controllers
     * violation sources.
     *
     * @param  string  $standard
     * @return array<int, string>
     */
    private function structureViolations(string $standard): array
    {
        $config  = new Config(['--standard=' . $standard, '--extensions=inc,php'], false);
        $ruleset = new Ruleset($config);
        $file    = new LocalFile(__DIR__ . DIRECTORY_SEPARATOR . 'PackageMisnamed.inc', $ruleset, $config);

        $file->process();

        return array_values(array_filter(
            $this->sources($file),
            static fn (string $source): bool => str_starts_with($source, 'SineMaculaLaravel.Structure')
                || str_starts_with($source, 'SineMaculaLaravel.Controllers'),
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
