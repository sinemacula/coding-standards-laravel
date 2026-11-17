<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireFillableCastsRule;

/**
 * Tests for the fillable casts rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireFillableCastsRule>
 *
 * @internal
 */
#[CoversClass(RequireFillableCastsRule::class)]
final class RequireFillableCastsRuleTest extends RuleTestCase
{
    /**
     * Flags each fillable attribute without a matching cast.
     *
     * @return void
     */
    public function testFlagsFillableAttributesWithoutCasts(): void
    {
        $this->analyse([__DIR__ . '/data/fillable-casts.inc'], [
            [
                'Fillable attribute "email" must declare a cast in the casts() method.',
                23,
            ],
            [
                'Fillable attribute "active" must declare a cast in the casts() method.',
                23,
            ],
            [
                'Fillable attribute "title" must declare a cast in the casts() method.',
                35,
            ],
            [
                'Fillable attribute "quantity" must declare a cast in the casts() method.',
                38,
            ],
            [
                'Fillable attribute "role" must declare a cast in the casts() method.',
                103,
            ],
            [
                'Fillable attribute "label" must declare a cast in the casts() method.',
                167,
            ],
        ]);
    }

    /**
     * A model declared in a test file is exempt.
     *
     * @return void
     */
    public function testExemptsModelsDeclaredInTests(): void
    {
        $this->analyse([__DIR__ . '/data/tests/fillable-casts-tests.inc'], []);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new RequireFillableCastsRule;
    }
}
