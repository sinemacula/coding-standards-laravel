<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsRelationships;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowModelBehaviourRule;

/**
 * Tests for the model surface rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowModelBehaviourRule>
 *
 * @internal
 */
#[CoversClass(DisallowModelBehaviourRule::class)]
#[CoversTrait(DetectsRelationships::class)]
final class DisallowModelBehaviourRuleTest extends RuleTestCase
{
    /** @var string The behaviour message. */
    private const string BEHAVIOUR = 'Method "%s()" puts behaviour on a model; move it to a repository or a '
        . 'service. A model carries its schema and its relations.';

    /** @var string The behaviour message for a method a trait imports. */
    private const string IMPORTED_BEHAVIOUR = 'Method "%s()", imported from a trait, puts behaviour on a model; '
        . 'move it to a repository or a service. A model carries its schema and its relations.';

    /** @var string The query scope message. */
    private const string SCOPE = 'Query scope "%s()" belongs on a repository, which can compose it as a fluent '
        . 'method, not on the model.';

    /** @var string The query scope message for a scope a trait imports. */
    private const string IMPORTED_SCOPE = 'Query scope "%s()", imported from a trait, belongs on a repository, '
        . 'which can compose it as a fluent method, not on the model.';

    /** @var array<int, string>|null Permitted hooks for the rule under test, or null for the default. */
    private ?array $hooks = null;

    /**
     * Flags behaviour and query scopes on a model, not its permitted surface.
     *
     * @return void
     */
    public function testFlagsBehaviourOnAModel(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour.inc'], [
            [sprintf(self::SCOPE, 'live'), 59],
            [sprintf(self::BEHAVIOUR, 'revoke'), 65],
            [sprintf(self::BEHAVIOUR, 'scopeExpired'), 70],
            [sprintf(self::BEHAVIOUR, 'casts'), 78],
        ]);
    }

    /**
     * Honours a configured hook set in place of the default.
     *
     * @return void
     */
    public function testHonoursAConfiguredHookSet(): void
    {
        $this->hooks = ['casts'];

        $this->analyse([__DIR__ . '/data/model-behaviour.inc'], [
            [sprintf(self::SCOPE, 'live'), 59],
            [sprintf(self::BEHAVIOUR, 'getRouteKeyName'), 39],
            [sprintf(self::BEHAVIOUR, 'revoke'), 65],
            [sprintf(self::BEHAVIOUR, 'scopeExpired'), 70],
        ]);
    }

    /**
     * Resolves a model through its ancestry; only opted-in interfaces exempt.
     *
     * @return void
     */
    public function testResolvesAModelThroughItsAncestry(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour-inheritance.inc'], [
            [sprintf(self::BEHAVIOUR, 'renew'), 25],
            [sprintf(self::BEHAVIOUR, 'purge'), 76],
            [sprintf(self::BEHAVIOUR, 'purge'), 81],
            [sprintf(self::BEHAVIOUR, 'expire'), 112],
            [sprintf(self::BEHAVIOUR, 'toArray'), 120],
            [sprintf(self::BEHAVIOUR, 'jsonSerialize'), 125],
            [sprintf(self::BEHAVIOUR, 'offsetGet'), 130],
            [sprintf(self::BEHAVIOUR, 'getAuthIdentifier'), 151],
        ]);
    }

    /**
     * Every default hook is reported once the configured set omits it.
     *
     * @return void
     */
    public function testTheDefaultHookSetCoversTheFrameworkHooks(): void
    {
        $this->hooks = [];

        $this->analyse([__DIR__ . '/data/model-behaviour-inheritance.inc'], [
            [sprintf(self::BEHAVIOUR, 'renew'), 25],
            [sprintf(self::BEHAVIOUR, 'uniqueIds'), 33],
            [sprintf(self::BEHAVIOUR, 'newUniqueId'), 38],
            [sprintf(self::BEHAVIOUR, 'getRouteKeyName'), 43],
            [sprintf(self::BEHAVIOUR, 'resolveRouteBindingQuery'), 48],
            [sprintf(self::BEHAVIOUR, 'getMorphClass'), 53],
            [sprintf(self::BEHAVIOUR, 'prunable'), 58],
            [sprintf(self::BEHAVIOUR, 'getTable'), 63],
            [sprintf(self::BEHAVIOUR, 'getConnectionName'), 68],
            [sprintf(self::BEHAVIOUR, 'purge'), 76],
            [sprintf(self::BEHAVIOUR, 'purge'), 81],
            [sprintf(self::BEHAVIOUR, 'expire'), 112],
            [sprintf(self::BEHAVIOUR, 'toArray'), 120],
            [sprintf(self::BEHAVIOUR, 'jsonSerialize'), 125],
            [sprintf(self::BEHAVIOUR, 'offsetGet'), 130],
            [sprintf(self::BEHAVIOUR, 'getAuthIdentifier'), 151],
        ]);
    }

    /**
     * Reports the framework methods that fail a default-hook test.
     *
     * @return void
     */
    public function testReportsTheMethodsTheDefaultHookSetLeavesOut(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour-hooks.inc'], [
            [sprintf(self::BEHAVIOUR, 'casts'), 9],
            [sprintf(self::BEHAVIOUR, 'boot'), 14],
            [sprintf(self::BEHAVIOUR, 'booting'), 19],
            [sprintf(self::BEHAVIOUR, 'booted'), 24],
            [sprintf(self::BEHAVIOUR, 'newFactory'), 29],
            [sprintf(self::BEHAVIOUR, 'newCollection'), 34],
            [sprintf(self::BEHAVIOUR, 'newEloquentBuilder'), 39],
            [sprintf(self::BEHAVIOUR, 'toSearchableArray'), 44],
            [sprintf(self::BEHAVIOUR, 'resolveRouteBinding'), 52],
            [sprintf(self::BEHAVIOUR, 'resolveSoftDeletableRouteBinding'), 57],
            [sprintf(self::BEHAVIOUR, 'resolveChildRouteBinding'), 62],
            [sprintf(self::BEHAVIOUR, 'resolveSoftDeletableChildRouteBinding'), 67],
        ]);
    }

    /**
     * Appended route binding resolvers are permitted alongside the defaults.
     *
     * @return void
     */
    public function testPermitsTheRouteBindingResolversOnceAppended(): void
    {
        $default = (new \ReflectionParameter([DisallowModelBehaviourRule::class, '__construct'], 'hooks'))
            ->getDefaultValue();

        self::assertIsArray($default);

        $this->hooks = [
            ...array_values(array_filter($default, is_string(...))),
            'resolveRouteBinding',
            'resolveSoftDeletableRouteBinding',
            'resolveChildRouteBinding',
            'resolveSoftDeletableChildRouteBinding',
        ];

        $this->analyse([__DIR__ . '/data/model-behaviour-hooks.inc'], [
            [sprintf(self::BEHAVIOUR, 'casts'), 9],
            [sprintf(self::BEHAVIOUR, 'boot'), 14],
            [sprintf(self::BEHAVIOUR, 'booting'), 19],
            [sprintf(self::BEHAVIOUR, 'booted'), 24],
            [sprintf(self::BEHAVIOUR, 'newFactory'), 29],
            [sprintf(self::BEHAVIOUR, 'newCollection'), 34],
            [sprintf(self::BEHAVIOUR, 'newEloquentBuilder'), 39],
            [sprintf(self::BEHAVIOUR, 'toSearchableArray'), 44],
        ]);
    }

    /**
     * Reads trait-imported methods and anonymous classes.
     *
     * Imported methods report on the class line, as the trait may be elsewhere.
     *
     * @return void
     */
    public function testReadsImportedAndAnonymousDeclarations(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour-declared.inc'], [
            [sprintf(self::IMPORTED_SCOPE, 'live'), 28],
            [sprintf(self::IMPORTED_BEHAVIOUR, 'carry'), 28],
            [sprintf(self::BEHAVIOUR, 'detonate'), 39],
        ]);
    }

    /**
     * Reads the parent name as written alongside the resolved ancestry.
     *
     * @return void
     */
    public function testReadsTheParentNameAlongsideTheResolvedAncestry(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour-partial.inc'], [
            [sprintf(self::BEHAVIOUR, 'escalate'), 14],
        ]);
    }

    /**
     * Falls back to the parent name when the ancestry does not resolve.
     *
     * @return void
     */
    public function testFallsBackToTheParentNameWhenTheAncestryDoesNotResolve(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour-unresolved.inc'], [
            [sprintf(self::BEHAVIOUR, 'revoke'), 10],
            [sprintf(self::BEHAVIOUR, 'relink'), 26],
        ]);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PHPStan\Node\ClassMethodsNode>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return $this->hooks === null ? new DisallowModelBehaviourRule : new DisallowModelBehaviourRule($this->hooks);
    }
}
