<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsTestClasses;
use SineMaculaLaravel\Sniffs\Configuration\DisallowEnvOutsideConfigSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the env-outside-config sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowEnvOutsideConfigSniff::class)]
#[CoversTrait(DetectsTestClasses::class)]
final class DisallowEnvOutsideConfigSniffTest extends AbstractSniffTestCase
{
    /**
     * env() is flagged outside config/ in any letter case, while config() and
     * same-named method calls are not.
     *
     * @return void
     */
    public function testFlagsEnvOutsideConfigFiles(): void
    {
        $this->assertErrorsOnLines('DisallowEnvOutsideConfig.inc', [11, 14]);
    }

    /**
     * env() is allowed in a file that lives inside a config/ directory.
     *
     * @return void
     */
    public function testAllowsEnvInsideConfigFiles(): void
    {
        $this->assertErrorsOnLines('config/EnvInConfig.inc', []);
    }

    /**
     * env() is allowed in a config/ path written with Windows separators.
     *
     * @return void
     */
    public function testAllowsEnvInAWindowsStyleConfigPath(): void
    {
        // A literal backslash in the fixture name simulates a Windows path.
        $fixture = 'winpath\config\EnvBackslashConfig.inc';

        file_put_contents(__DIR__ . '/' . $fixture, "<?php\n\n\$value = env('APP_ENV');\n");

        try {
            $this->assertErrorsOnLines($fixture, []);
        } finally {
            unlink(__DIR__ . '/' . $fixture);
        }
    }

    /**
     * env() is allowed in a testbench TestCase, which reads it to boot the app.
     *
     * @return void
     */
    public function testAllowsEnvInATestCaseClass(): void
    {
        $this->assertErrorsOnLines('EnvInTestCase.inc', []);
    }

    /**
     * env() is allowed in a class whose name ends in `Test`.
     *
     * @return void
     */
    public function testAllowsEnvInATestSuffixedClass(): void
    {
        $this->assertErrorsOnLines('EnvInTestSuffix.inc', []);
    }

    /**
     * env() is allowed in a test class declared directly after the open tag.
     *
     * @return void
     */
    public function testAllowsEnvInATestClassOnTheOpenTagLine(): void
    {
        $this->assertErrorsOnLines('EnvImmediateTestClass.inc', []);
    }

    /**
     * env() is allowed in any file that lives under a tests/ directory.
     *
     * @return void
     */
    public function testAllowsEnvUnderTheTestsDirectory(): void
    {
        $this->assertErrorsOnLines('tests/EnvUnderTestsDir.inc', []);
    }

    /**
     * env() is allowed in a classless script under a tests/ directory.
     *
     * @return void
     */
    public function testAllowsEnvInAClasslessFileUnderTests(): void
    {
        $this->assertErrorsOnLines('tests/EnvStatementsUnderTestsDir.inc', []);
    }

    /**
     * env() is allowed in a tests/ path written with Windows separators.
     *
     * @return void
     */
    public function testAllowsEnvInAWindowsStyleTestsPath(): void
    {
        // A literal backslash in the fixture name simulates a Windows path.
        $fixture = 'winpath\tests\EnvBackslashTests.inc';

        file_put_contents(__DIR__ . '/' . $fixture, "<?php\n\n\$value = env('APP_ENV');\n");

        try {
            $this->assertErrorsOnLines($fixture, []);
        } finally {
            unlink(__DIR__ . '/' . $fixture);
        }
    }
}
