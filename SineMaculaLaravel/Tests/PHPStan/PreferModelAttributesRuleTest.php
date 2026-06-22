<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
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
#[CoversClass(PreferModelAttributesRule::class)]
final class PreferModelAttributesRuleTest extends RuleTestCase
{
    /** @var array<int, string> Attributes the rule under test mandates. */
    private array $attributes = ['Table', 'Fillable', 'Hidden'];

    /**
     * The default expressive set flags $table/$fillable/$hidden; a $hidden over
     * the limit, the disabled attributes, and non-models are not.
     *
     * @return void
     */
    public function testFlagsTheDefaultExpressiveSet(): void
    {
        $this->analyse([__DIR__ . '/data/prefer-model-attributes.inc'], [
            ['Use the #[Table] attribute instead of the $table property.', 9],
            ['Use the #[Hidden] attribute instead of the $hidden property.', 11],
            ['Use the #[Fillable] attribute instead of the $fillable property.', 15],
        ]);
    }

    /**
     * A project enables only the attributes its Laravel version provides; the
     * configured set is honoured for both properties and method overrides.
     *
     * @return void
     */
    public function testHonoursAConfiguredSet(): void
    {
        $this->attributes = ['Touches', 'UseFactory', 'CollectedBy', 'UseEloquentBuilder'];

        $this->analyse([__DIR__ . '/data/prefer-model-attributes.inc'], [
            ['Use the #[Touches] attribute instead of the $touches property.', 13],
            ['Use the #[UseFactory] attribute instead of overriding the newFactory() method.', 17],
            ['Use the #[CollectedBy] attribute instead of overriding the newCollection() method.', 28],
            ['Use the #[UseEloquentBuilder] attribute instead of overriding the newEloquentBuilder() method.', 32],
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
        return new PreferModelAttributesRule($this->attributes);
    }
}
