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
     * A public method carrying logic and a query scope in either form are
     * reported; relations, the framework hooks at either visibility, accessors
     * declared by their return type, non-public helpers and non-models are not.
     *
     * @return void
     */
    public function testFlagsBehaviourOnAModel(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour.inc'], [
            [sprintf(self::SCOPE, 'live'), 59],
            [sprintf(self::BEHAVIOUR, 'revoke'), 65],
            [sprintf(self::BEHAVIOUR, 'scopeExpired'), 70],
        ]);
    }

    /**
     * The permitted hooks are configurable, so a project on a version with a
     * different set is not forced to keep the default one.
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
     * A model is recognised through its whole ancestry, so behaviour on a model
     * sitting behind the project's own base class is reported; a class whose
     * ancestry reaches no model base is still left alone. A method an interface
     * the model's author opted into declares is permitted, including one that
     * interface inherits from another, while the contracts the framework base
     * already carries exempt nothing: `toArray()`, `jsonSerialize()` and the
     * array-access methods are on every model there is and are the usual way
     * behaviour is smuggled onto one. Every framework base in the ancestry is
     * read for that, so the contracts a model gains from `Authenticatable`
     * exempt nothing either. A method an inherited abstract mandates is not
     * exempt: the rule reports the mandate where it is declared, and reports
     * each implementation of it too.
     *
     * @return void
     */
    public function testResolvesAModelThroughItsAncestry(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour-inheritance.inc'], [
            [sprintf(self::BEHAVIOUR, 'renew'), 25],
            [sprintf(self::BEHAVIOUR, 'purge'), 106],
            [sprintf(self::BEHAVIOUR, 'purge'), 111],
            [sprintf(self::BEHAVIOUR, 'expire'), 142],
            [sprintf(self::BEHAVIOUR, 'toArray'), 150],
            [sprintf(self::BEHAVIOUR, 'jsonSerialize'), 155],
            [sprintf(self::BEHAVIOUR, 'offsetGet'), 160],
            [sprintf(self::BEHAVIOUR, 'getAuthIdentifier'), 181],
        ]);
    }

    /**
     * The hooks the default set names are permitted on a model reached through
     * an intermediate base, and reported once the set no longer names them, so
     * every entry in the default earns its place.
     *
     * @return void
     */
    public function testTheDefaultHookSetCoversTheFrameworkHooks(): void
    {
        $this->hooks = [];

        $this->analyse([__DIR__ . '/data/model-behaviour-inheritance.inc'], [
            [sprintf(self::BEHAVIOUR, 'renew'), 25],
            [sprintf(self::BEHAVIOUR, 'casts'), 33],
            [sprintf(self::BEHAVIOUR, 'boot'), 38],
            [sprintf(self::BEHAVIOUR, 'booting'), 43],
            [sprintf(self::BEHAVIOUR, 'booted'), 48],
            [sprintf(self::BEHAVIOUR, 'newFactory'), 53],
            [sprintf(self::BEHAVIOUR, 'newCollection'), 58],
            [sprintf(self::BEHAVIOUR, 'newEloquentBuilder'), 63],
            [sprintf(self::BEHAVIOUR, 'uniqueIds'), 68],
            [sprintf(self::BEHAVIOUR, 'newUniqueId'), 73],
            [sprintf(self::BEHAVIOUR, 'getRouteKeyName'), 78],
            [sprintf(self::BEHAVIOUR, 'resolveRouteBindingQuery'), 83],
            [sprintf(self::BEHAVIOUR, 'getMorphClass'), 88],
            [sprintf(self::BEHAVIOUR, 'prunable'), 93],
            [sprintf(self::BEHAVIOUR, 'toSearchableArray'), 98],
            [sprintf(self::BEHAVIOUR, 'purge'), 106],
            [sprintf(self::BEHAVIOUR, 'purge'), 111],
            [sprintf(self::BEHAVIOUR, 'expire'), 142],
            [sprintf(self::BEHAVIOUR, 'toArray'), 150],
            [sprintf(self::BEHAVIOUR, 'jsonSerialize'), 155],
            [sprintf(self::BEHAVIOUR, 'offsetGet'), 160],
            [sprintf(self::BEHAVIOUR, 'getAuthIdentifier'), 181],
        ]);
    }

    /**
     * Every method the class declares is read, so a trait is not a way of
     * carrying behaviour past the rule and neither is an anonymous class. An
     * imported method is reported against the line the class declares itself
     * on, since the trait may live in another file, and the permitted hooks
     * still apply to it.
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
     * The parent name as written is read alongside the resolved ancestry rather
     * than only when nothing resolved, so a model behind a base of the
     * project's own named for the framework base stays covered where the
     * framework base itself is invisible. A base named something else is the
     * shape name-based recovery cannot reach, and is left alone.
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
     * Where the ancestry resolves to nothing at all, the parent name as written
     * decides, so a model extending the framework base is still covered instead
     * of quietly exempting itself, and a class extending something else is
     * still left alone.
     *
     * @return void
     */
    public function testFallsBackToTheParentNameWhenTheAncestryDoesNotResolve(): void
    {
        $this->analyse([__DIR__ . '/data/model-behaviour-unresolved.inc'], [
            [sprintf(self::BEHAVIOUR, 'revoke'), 10],
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
