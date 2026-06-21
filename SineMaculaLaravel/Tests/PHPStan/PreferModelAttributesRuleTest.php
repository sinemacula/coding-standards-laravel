<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\PreferModelAttributesRule;

/**
 * Tests for the model attribute preference rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\PreferModelAttributesRule>
 *
 * @internal
 */
#[CoversNothing]
final class PreferModelAttributesRuleTest extends RuleTestCase
{
    /**
     * Model properties and method overrides with an attribute equivalent are
     * flagged; $hidden over the limit, other members and non-models are not.
     *
     * @return void
     */
    public function testFlagsLegacyModelProperties(): void
    {
        $this->analyse([__DIR__ . '/data/prefer-model-attributes.inc'], [
            [
                'Use the #[Table] attribute instead of the $table property.',
                9,
            ],
            [
                'Use the #[Hidden] attribute instead of the $hidden property.',
                11,
            ],
            [
                'Use the #[Touches] attribute instead of the $touches property.',
                13,
            ],
            [
                'Use the #[UseFactory] attribute instead of overriding the newFactory() method.',
                17,
            ],
            [
                'Use the #[CollectedBy] attribute instead of overriding the newCollection() method.',
                28,
            ],
            [
                'Use the #[UseEloquentBuilder] attribute instead of overriding the newEloquentBuilder() method.',
                32,
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
        return new PreferModelAttributesRule;
    }
}
