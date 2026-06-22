<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
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
final class DisallowEnvOutsideConfigSniffTest extends AbstractSniffTestCase
{
    /**
     * env() is flagged outside config/, while config() and same-named method
     * calls are not.
     *
     * @return void
     */
    public function testFlagsEnvOutsideConfigFiles(): void
    {
        $this->assertErrorsOnLines('DisallowEnvOutsideConfig.inc', [11]);
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
     * env() is allowed in any file that lives under a tests/ directory.
     *
     * @return void
     */
    public function testAllowsEnvUnderTheTestsDirectory(): void
    {
        $this->assertErrorsOnLines('tests/EnvUnderTestsDir.inc', []);
    }
}
