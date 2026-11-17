<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * Flags env() outside config/ in any letter case.
     *
     * @return void
     */
    public function testFlagsEnvOutsideConfigFiles(): void
    {
        $this->assertErrorsOnLines('DisallowEnvOutsideConfig.inc', [11, 14]);
    }

    /**
     * Fixtures in which env() is allowed.
     *
     * @return iterable<string, array{string}>
     */
    public static function exemptFixtures(): iterable
    {
        yield 'config directory' => ['config/EnvInConfig.inc'];
        yield 'testbench TestCase' => ['EnvInTestCase.inc'];
        yield 'Test-suffixed class' => ['EnvInTestSuffix.inc'];
        yield 'test class on the open tag line' => ['EnvImmediateTestClass.inc'];
        yield 'tests directory' => ['tests/EnvUnderTestsDir.inc'];
        yield 'classless script under tests' => ['tests/EnvStatementsUnderTestsDir.inc'];
    }

    /**
     * env() is allowed in config files and test code.
     *
     * @param  string  $fixture
     * @return void
     */
    #[DataProvider('exemptFixtures')]
    public function testAllowsEnvInExemptFiles(string $fixture): void
    {
        $this->assertErrorsOnLines($fixture, []);
    }

    /**
     * Fixture names whose literal backslashes simulate a Windows path.
     *
     * @return iterable<string, array{string}>
     */
    public static function windowsFixtures(): iterable
    {
        yield 'config directory' => ['winpath\config\EnvBackslashConfig.inc'];
        yield 'tests directory' => ['winpath\tests\EnvBackslashTests.inc'];
    }

    /**
     * env() is allowed in exempt paths written with Windows separators.
     *
     * @param  string  $fixture
     * @return void
     */
    #[DataProvider('windowsFixtures')]
    public function testAllowsEnvInWindowsStylePaths(string $fixture): void
    {
        file_put_contents(__DIR__ . '/' . $fixture, "<?php\n\n\$value = env('APP_ENV');\n");

        try {
            $this->assertErrorsOnLines($fixture, []);
        } finally {
            unlink(__DIR__ . '/' . $fixture);
        }
    }
}
