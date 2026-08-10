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

    /** @var string The expected error message. */
    private const string FILLABLE_ERROR = 'Use the #[Fillable] attribute instead of the $fillable property.';

    /** @var string The expected error message. */
    private const string HIDDEN_ERROR = 'Use the #[Hidden] attribute instead of the $hidden property.';

    /** @var string The expected error message. */
    private const string COLLECTED_BY_ERROR = 'Use the #[CollectedBy] attribute instead of overriding the newCollection() method.';

    /** @var string The fixture carrying every legacy form. */
    private const string FIXTURE = __DIR__ . '/data/prefer-model-attributes.inc';

    /** @var string A model whose composer.json floor is below 13.2. */
    private const string UNSUPPORTED_MODEL = __DIR__ . '/data/version/unsupported/model.inc';

    /** @var string A model whose floor is below 13.2 but which resolves above it. */
    private const string DRIFT_MODEL = __DIR__ . '/data/version/drift/model.inc';

    /** @var array<int, string>|null Attributes the rule under test mandates, or null for the constructor default. */
    private ?array $attributes = null;

    /** @var string Explicit Laravel floor for the rule under test. */
    private string $minLaravelVersion = '';

    /**
     * On a supporting version the constructor's default expressive set flags
     * $table/$fillable/$hidden - including a $fillable declared alongside an
     * exempt property and a $hidden at exactly the field limit; a $hidden over
     * the limit, the disabled attributes, a property with no attribute
     * equivalent and non-models are not.
     *
     * @return void
     */
    public function testFlagsTheExpressiveSetWhenSupported(): void
    {
        $this->minLaravelVersion = '13.2';

        $this->analyse([self::FIXTURE], [
            [self::TABLE_ERROR, 9],
            [self::HIDDEN_ERROR, 11],
            [self::FILLABLE_ERROR, 15],
            [self::FILLABLE_ERROR, 53],
            [self::FILLABLE_ERROR, 66],
            [self::HIDDEN_ERROR, 71],
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

        $this->analyse([self::FIXTURE], [
            ['Use the #[Touches] attribute instead of the $touches property.', 13],
            ['Use the #[UseFactory] attribute instead of overriding the newFactory() method.', 17],
            [self::COLLECTED_BY_ERROR, 28],
            ['Use the #[UseEloquentBuilder] attribute instead of overriding the newEloquentBuilder() method.', 32],
            ['Use the #[Touches] attribute instead of the $touches property.', 53],
            [self::COLLECTED_BY_ERROR, 59],
            ['Use the #[UseFactory] attribute instead of overriding the newFactory() method.', 81],
            [self::COLLECTED_BY_ERROR, 85],
        ]);
    }

    /**
     * A method override whose attribute is not enabled does not stop the ones
     * after it from being reported.
     *
     * @return void
     */
    public function testKeepsScanningPastAnUnconfiguredMethodOverride(): void
    {
        $this->attributes = ['CollectedBy'];

        $this->analyse([self::FIXTURE], [
            [self::COLLECTED_BY_ERROR, 28],
            [self::COLLECTED_BY_ERROR, 59],
            [self::COLLECTED_BY_ERROR, 85],
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
            [self::HIDDEN_ERROR, 11],
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
     * rather than an error, and no lagging-floor notice either - a floor that
     * cannot be read is not evidence that it lags.
     *
     * @return void
     */
    public function testUnparseableExplicitVersionDoesNotEnforce(): void
    {
        $this->minLaravelVersion = 'not-a-version';

        $this->analyse([self::DRIFT_MODEL], []);
    }

    /**
     * A floor below 13.2 on a project whose composer.lock already resolves
     * above it reports the lagging floor rather than staying silent, so the
     * migration is not deferred wholesale to the day the floor is raised.
     *
     * @return void
     */
    public function testReportsALaggingFloorWhenTheLockAlreadySupportsTheAttribute(): void
    {
        $this->analyse([self::DRIFT_MODEL], [
            [
                '#[Table] is available in the Laravel 13.23.0 this project resolves to, but its declared floor '
                . 'still allows older versions; raise the floor to 13.2 to replace the $table property.',
                9,
            ],
        ]);
    }

    /**
     * The locked illuminate/database version wins over laravel/framework, as
     * the declared constraint does.
     *
     * @return void
     */
    public function testPrefersTheLockedIlluminateDatabaseVersion(): void
    {
        $this->analyse([__DIR__ . '/data/version/drift-illuminate/model.inc'], [
            [
                '#[Table] is available in the Laravel 13.24.1 this project resolves to, but its declared floor '
                . 'still allows older versions; raise the floor to 13.2 to replace the $table property.',
                9,
            ],
        ]);
    }

    /**
     * A project that both declares and resolves below 13.2 genuinely cannot use
     * the attribute, so it stays silent.
     *
     * @return void
     */
    public function testStaysSilentWhenTheLockAlsoPredatesTheAttribute(): void
    {
        $this->analyse([__DIR__ . '/data/version/drift-old/model.inc'], []);
    }

    /**
     * A composer.lock carrying no readable Laravel entry is treated as no
     * resolved version at all.
     *
     * @return void
     */
    public function testMalformedLockYieldsNoLaggingFloorNotice(): void
    {
        $this->analyse([__DIR__ . '/data/version/drift-malformed/model.inc'], []);
    }

    /**
     * A floor that already reaches 13.2 reports the plain replacement, never
     * the lagging-floor notice, even with a lock present.
     *
     * @return void
     */
    public function testASupportedFloorIsNeverReportedAsLagging(): void
    {
        $this->minLaravelVersion = '13.2';

        $this->analyse([self::DRIFT_MODEL], [
            [self::TABLE_ERROR, 9],
        ]);
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
