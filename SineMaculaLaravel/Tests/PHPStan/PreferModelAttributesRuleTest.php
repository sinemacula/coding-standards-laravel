<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsLaravelVersion;
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
#[CoversTrait(DetectsLaravelVersion::class)]
final class PreferModelAttributesRuleTest extends RuleTestCase
{
    /** @var string The expected-property error message. */
    private const string TABLE_ERROR = 'Use the #[Table] attribute instead of the $table property.';

    /** @var string A model whose composer.json floor is below 13.2. */
    private const string UNSUPPORTED_MODEL = __DIR__ . '/data/version/unsupported/model.inc';

    /** @var array<int, string>|null Attributes the rule under test mandates, or null for the constructor default. */
    private ?array $attributes = null;

    /** @var string Explicit Laravel floor for the rule under test. */
    private string $minLaravelVersion = '';

    /**
     * On a supporting version the constructor's default expressive set flags
     * $table/$fillable/$hidden - including a $fillable declared alongside an
     * exempt property and a $hidden at exactly the field limit; a $hidden over
     * the limit, the disabled attributes and non-models are not.
     *
     * @return void
     */
    public function testFlagsTheExpressiveSetWhenSupported(): void
    {
        $this->minLaravelVersion = '13.2';

        $this->analyse([__DIR__ . '/data/prefer-model-attributes.inc'], [
            [self::TABLE_ERROR, 9],
            ['Use the #[Hidden] attribute instead of the $hidden property.', 11],
            ['Use the #[Fillable] attribute instead of the $fillable property.', 15],
            ['Use the #[Fillable] attribute instead of the $fillable property.', 53],
            ['Use the #[Fillable] attribute instead of the $fillable property.', 66],
            ['Use the #[Hidden] attribute instead of the $hidden property.', 71],
        ]);
    }

    /**
     * A project enables only the attributes its version provides; the ungated
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
            ['Use the #[Touches] attribute instead of the $touches property.', 53],
            ['Use the #[CollectedBy] attribute instead of overriding the newCollection() method.', 59],
        ]);
    }

    /**
     * A composer.json declaring illuminate/database >= 13.2 enables the gated
     * attributes; the constraint is read from the nearest parent directory.
     *
     * @return void
     */
    public function testDetectsSupportedVersionFromComposer(): void
    {
        $this->analyse([__DIR__ . '/data/version/supported/app/model.inc'], [
            [self::TABLE_ERROR, 9],
            ['Use the #[Hidden] attribute instead of the $hidden property.', 11],
        ]);
    }

    /**
     * A ^12.0 || ^13.0 floor is below 13.2, so the property form is left alone
     * even though #[Table] is enabled.
     *
     * @return void
     */
    public function testDoesNotEnforceBelowTheFloor(): void
    {
        $this->analyse([self::UNSUPPORTED_MODEL], []);
    }

    /**
     * laravel/framework is read when illuminate/database is absent.
     *
     * @return void
     */
    public function testFallsBackToLaravelFramework(): void
    {
        $this->analyse([__DIR__ . '/data/version/framework/model.inc'], [
            [self::TABLE_ERROR, 9],
        ]);
    }

    /**
     * When both constraints are declared, illuminate/database wins over
     * laravel/framework - here it supplies the supporting floor.
     *
     * @return void
     */
    public function testPrefersIlluminateDatabaseOverFramework(): void
    {
        $this->analyse([__DIR__ . '/data/version/both/model.inc'], [
            [self::TABLE_ERROR, 9],
        ]);
    }

    /**
     * With no detectable version the gated attributes are never enforced, so an
     * attribute that may be unavailable is never flagged.
     *
     * @return void
     */
    public function testDefaultsToNotEnforcingWhenVersionUnknown(): void
    {
        $this->analyse([__DIR__ . '/data/version/unknown/model.inc'], []);
    }

    /**
     * An explicit minLaravelVersion overrides composer.json, enforcing gated
     * attributes regardless of the detected floor.
     *
     * @return void
     */
    public function testExplicitMinVersionOverridesComposer(): void
    {
        $this->minLaravelVersion = '13.2';

        $this->analyse([self::UNSUPPORTED_MODEL], [
            [self::TABLE_ERROR, 9],
        ]);
    }

    /**
     * An unparseable explicit minLaravelVersion yields no gated enforcement
     * rather than an error.
     *
     * @return void
     */
    public function testUnparseableExplicitVersionDoesNotEnforce(): void
    {
        $this->minLaravelVersion = 'not-a-version';

        $this->analyse([self::UNSUPPORTED_MODEL], []);
    }

    /**
     * An unparseable composer.json constraint is treated as undetectable, so
     * the gated attributes are not enforced.
     *
     * @return void
     */
    public function testUnparseableComposerConstraintDoesNotEnforce(): void
    {
        $this->analyse([__DIR__ . '/data/version/malformed/model.inc'], []);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        if ($this->attributes === null) {
            return new PreferModelAttributesRule(minLaravelVersion: $this->minLaravelVersion);
        }

        return new PreferModelAttributesRule($this->attributes, $this->minLaravelVersion);
    }
}
