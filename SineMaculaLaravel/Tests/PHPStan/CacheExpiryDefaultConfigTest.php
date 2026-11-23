<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Testing\PHPStanTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowModelBehaviourRule;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireCacheExpiryRule;

/**
 * Tests that the shipped config leaves the cache expiry rule off.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversNothing]
final class CacheExpiryDefaultConfigTest extends PHPStanTestCase
{
    /**
     * Leaves the rule unregistered until a project enables it.
     *
     * @return void
     */
    public function testLeavesTheRuleOffByDefault(): void
    {
        $rules = array_map(
            static fn (mixed $rule): string => is_object($rule) ? $rule::class : '',
            self::getContainer()->getServicesByTag('phpstan.rules.rule'),
        );

        self::assertContains(DisallowModelBehaviourRule::class, $rules);
        self::assertNotContains(RequireCacheExpiryRule::class, $rules);
    }

    /**
     * The shipped config, unchanged.
     *
     * @return array<int, string>
     */
    #[\Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [dirname(__DIR__, 3) . '/php/phpstan-laravel.neon'];
    }
}
