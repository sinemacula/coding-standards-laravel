<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireCacheExpiryRule;

/**
 * Tests for the cache expiry rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireCacheExpiryRule>
 */
#[CoversClass(RequireCacheExpiryRule::class)]
final class RequireCacheExpiryRuleTest extends RuleTestCase
{
    /** @var string The error for a method that never expires. */
    private const string NEVER_EXPIRES = 'Cache write "%s()" never expires; store the key with a TTL.';

    /** @var string The error for a write without a TTL. */
    private const string NO_EXPIRY = 'Cache write "%s()" has no expiry; pass a TTL.';

    /** @var array{array<int, string>, array<string, int>}|null Forbidden and TTL methods, or null for the defaults. */
    private ?array $lists = null;

    /**
     * Flags every write that stores a cache key without an expiry.
     *
     * @return void
     */
    public function testFlagsCacheWritesWithoutAnExpiry(): void
    {
        $this->analyse([__DIR__ . '/data/cache-expiry.inc'], [
            [sprintf(self::NEVER_EXPIRES, 'forever'), 40],
            [sprintf(self::NEVER_EXPIRES, 'rememberForever'), 41],
            [sprintf(self::NEVER_EXPIRES, 'sear'), 42],
            [sprintf(self::NEVER_EXPIRES, 'FOREVER'), 43],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 44],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 45],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 46],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 47],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 48],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 49],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 50],
            [sprintf(self::NEVER_EXPIRES, 'forever'), 51],
            [sprintf(self::NO_EXPIRY, 'put'), 58],
            [sprintf(self::NO_EXPIRY, 'put'), 59],
            [sprintf(self::NO_EXPIRY, 'put'), 62],
            [sprintf(self::NO_EXPIRY, 'put'), 64],
            [sprintf(self::NO_EXPIRY, 'put'), 67],
            [sprintf(self::NO_EXPIRY, 'put'), 69],
            [sprintf(self::NO_EXPIRY, 'add'), 71],
            [sprintf(self::NO_EXPIRY, 'remember'), 73],
            [sprintf(self::NO_EXPIRY, 'rememberWithWarmth'), 76],
            [sprintf(self::NO_EXPIRY, 'set'), 77],
            [sprintf(self::NO_EXPIRY, 'setMultiple'), 79],
            [sprintf(self::NO_EXPIRY, 'putMany'), 80],
            [sprintf(self::NO_EXPIRY, 'put'), 82],
            [sprintf(self::NO_EXPIRY, 'put'), 85],
            [sprintf(self::NO_EXPIRY, 'put'), 86],
            [sprintf(self::NO_EXPIRY, 'add'), 87],
            [sprintf(self::NO_EXPIRY, 'put'), 88],
            [sprintf(self::NO_EXPIRY, 'lock'), 96],
            [sprintf(self::NO_EXPIRY, 'lock'), 97],
            [sprintf(self::NO_EXPIRY, 'lock'), 98],
            [sprintf(self::NO_EXPIRY, 'lock'), 101],
            [sprintf(self::NO_EXPIRY, 'lock'), 103],
            [sprintf(self::NO_EXPIRY, 'cache'), 111],
            [sprintf(self::NO_EXPIRY, 'cache'), 112],
            [sprintf(self::NO_EXPIRY, 'Lock'), 123],
            [sprintf(self::NO_EXPIRY, 'put'), 126],
            [sprintf(self::NO_EXPIRY, 'put'), 128],
            [sprintf(self::NO_EXPIRY, 'cache'), 129],
            [sprintf(self::NO_EXPIRY, 'put'), 132],
            [sprintf(self::NO_EXPIRY, 'put'), 133],
            [sprintf(self::NO_EXPIRY, 'remember'), 137],
            [sprintf(self::NO_EXPIRY, 'add'), 140],
            [sprintf(self::NO_EXPIRY, 'put'), 141],
        ]);
    }

    /**
     * Leaves a namespaced function named cache alone.
     *
     * @SuppressWarnings("php:S4833")
     *
     * @return void
     */
    public function testIgnoresANamespacedCacheFunction(): void
    {
        require_once __DIR__ . '/data/cache-expiry-namespaced-function.inc';

        $this->analyse([__DIR__ . '/data/cache-expiry-namespaced.inc'], []);
    }

    /**
     * Honours the methods a project configures in place of the defaults.
     *
     * @return void
     */
    public function testHonoursConfiguredMethods(): void
    {
        $this->lists = [['increment'], ['put' => 2, 'Refresh' => 1]];

        $this->analyse([__DIR__ . '/data/cache-expiry-config.inc'], [
            [sprintf(self::NEVER_EXPIRES, 'increment'), 8],
            [sprintf(self::NO_EXPIRY, 'put'), 10],
            [sprintf(self::NO_EXPIRY, 'cache'), 11],
            [sprintf(self::NO_EXPIRY, 'refresh'), 12],
        ]);
    }

    /**
     * Leaves the cache() helper alone once put needs no TTL.
     *
     * @return void
     */
    public function testLeavesTheHelperAloneWithoutAPutRequirement(): void
    {
        $this->lists = [[], ['add' => 2]];

        $this->analyse([__DIR__ . '/data/cache-expiry-config.inc'], [
            [sprintf(self::NO_EXPIRY, 'add'), 9],
        ]);
    }

    /**
     * Build the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Expr\CallLike>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return $this->lists === null
            ? new RequireCacheExpiryRule(self::createReflectionProvider())
            : new RequireCacheExpiryRule(self::createReflectionProvider(), ...$this->lists);
    }
}
