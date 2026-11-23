<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireCacheExpiryRule;

/**
 * Tests the cache expiry rule as enabled through the shipped config.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireCacheExpiryRule>
 */
#[CoversNothing]
final class CacheExpiryConfigTest extends RuleTestCase
{
    /**
     * Registers the rule once a project enables it.
     *
     * @return void
     */
    public function testRegistersTheRuleWhenEnabled(): void
    {
        $rules = array_filter(
            self::getContainer()->getServicesByTag('phpstan.rules.rule'),
            static fn (mixed $rule): bool => $rule instanceof RequireCacheExpiryRule,
        );

        self::assertCount(1, $rules);
    }

    /**
     * Appends to a list without `!` and replaces it with one.
     *
     * @return void
     */
    public function testMergesAndReplacesTheConfiguredLists(): void
    {
        $this->analyse([__DIR__ . '/data/cache-expiry-config.inc'], [
            ['Cache write "forever()" never expires; store the key with a TTL.', 7],
            ['Cache write "increment()" never expires; store the key with a TTL.', 8],
            ['Cache write "put()" has no expiry; pass a TTL.', 10],
            ['Cache write "cache()" has no expiry; pass a TTL.', 11],
        ]);
    }

    /**
     * The shipped config with the rule enabled.
     *
     * @return array<int, string>
     */
    #[\Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [
            dirname(__DIR__, 3) . '/php/phpstan-laravel.neon',
            __DIR__ . '/data/cache-expiry-enabled.neon',
        ];
    }

    /**
     * The rule as the container builds it.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Expr\CallLike>
     *
     * @throws \PHPStan\DependencyInjection\MissingServiceException
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(RequireCacheExpiryRule::class);
    }
}
