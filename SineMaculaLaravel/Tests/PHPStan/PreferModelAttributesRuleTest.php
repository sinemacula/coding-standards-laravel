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
    /** @var string The expected error message. */
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
     * Flags the default expressive set on a supporting version.
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
     * Honours a configured set for both properties and method overrides.
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
     * An unconfigured method override does not stop later ones being reported.
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
     * Enables gated attributes from the nearest composer.json's constraint.
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
     * A ^12.0 || ^13.0 floor is below 13.2, so the property form is left alone.
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
     * Prefers illuminate/database to laravel/framework when both are declared.
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
     * Never enforces gated attributes when no version is detectable.
     *
     * @return void
     */
    public function testDefaultsToNotEnforcingWhenVersionUnknown(): void
    {
        $this->analyse([__DIR__ . '/data/version/unknown/model.inc'], []);
    }

    /**
     * An explicit minLaravelVersion overrides composer.json.
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
     * An unparseable explicit floor enforces nothing and reports no lag.
     *
     * @return void
     */
    public function testUnparseableExplicitVersionDoesNotEnforce(): void
    {
        $this->minLaravelVersion = 'not-a-version';

        $this->analyse([self::DRIFT_MODEL], []);
    }

    /**
     * Reports a declared floor below 13.2 that the lock already resolves past.
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
     * Prefers the locked illuminate/database version over laravel/framework.
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
     * Stays silent when both the floor and the lock predate 13.2.
     *
     * @return void
     */
    public function testStaysSilentWhenTheLockAlsoPredatesTheAttribute(): void
    {
        $this->analyse([__DIR__ . '/data/version/drift-old/model.inc'], []);
    }

    /**
     * A lock with no readable Laravel entry resolves no version.
     *
     * @return void
     */
    public function testMalformedLockYieldsNoLaggingFloorNotice(): void
    {
        $this->analyse([__DIR__ . '/data/version/drift-malformed/model.inc'], []);
    }

    /**
     * A floor reaching 13.2 reports the plain replacement, even with a lock.
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
     * An unparseable composer.json constraint enforces no gated attributes.
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
