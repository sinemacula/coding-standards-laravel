<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowCastsPropertyRule;

/**
 * Tests for the $casts property rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowCastsPropertyRule>
 *
 * @internal
 */
#[CoversClass(DisallowCastsPropertyRule::class)]
final class DisallowCastsPropertyRuleTest extends RuleTestCase
{
    /**
     * The $casts property is flagged on an Eloquent model; a non-model class
     * with its own $casts, and a model without one, are not.
     *
     * @return void
     */
    public function testFlagsCastsProperty(): void
    {
        $this->analyse([__DIR__ . '/data/casts-property.inc'], [
            [
                'The $casts property is not allowed; declare casts via the casts() method instead.',
                9,
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
        return new DisallowCastsPropertyRule;
    }
}
