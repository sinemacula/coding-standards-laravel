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
}
