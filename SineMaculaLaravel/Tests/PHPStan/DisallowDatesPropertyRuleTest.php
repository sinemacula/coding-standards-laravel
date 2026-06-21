<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowDatesPropertyRule;

/**
 * Tests for the $dates property rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowDatesPropertyRule>
 *
 * @internal
 */
#[CoversClass(DisallowDatesPropertyRule::class)]
final class DisallowDatesPropertyRuleTest extends RuleTestCase
{
    /**
     * The $dates property is flagged; other properties are not.
     *
     * @return void
     */
    public function testFlagsDatesProperty(): void
    {
        $this->analyse([__DIR__ . '/data/dates-property.inc'], [
            [
                'The $dates property is deprecated; cast date attributes via the casts() method instead.',
                7,
            ],
        ]);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Property>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new DisallowDatesPropertyRule;
    }
}
