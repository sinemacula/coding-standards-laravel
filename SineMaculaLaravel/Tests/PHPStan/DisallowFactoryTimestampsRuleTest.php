<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowFactoryTimestampsRule;

/**
 * Tests for the factory timestamps rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowFactoryTimestampsRule>
 *
 * @internal
 */
#[CoversClass(DisallowFactoryTimestampsRule::class)]
final class DisallowFactoryTimestampsRuleTest extends RuleTestCase
{
    /**
     * Timestamp keys in a factory definition() are flagged; a clean factory, a
     * non-array return, a missing definition() and non-factories are not.
     *
     * @return void
     */
    public function testFlagsTimestampsInDefinition(): void
    {
        $this->analyse([__DIR__ . '/data/factory-timestamps.inc'], [
            [
                'A factory definition() must not set created_at; Eloquent manages timestamps.',
                13,
            ],
            [
                'A factory definition() must not set updated_at; Eloquent manages timestamps.',
                14,
            ],
        ]);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new DisallowFactoryTimestampsRule;
    }
}
