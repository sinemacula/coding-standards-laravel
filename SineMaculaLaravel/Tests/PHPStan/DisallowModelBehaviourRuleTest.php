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
            [
                'Query scope "live()" belongs on a repository, which can compose it as a fluent method, '
                . 'not on the model.',
                59,
            ],
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
            [
                'Query scope "live()" belongs on a repository, which can compose it as a fluent method, '
                . 'not on the model.',
                59,
            ],
            [sprintf(self::BEHAVIOUR, 'getRouteKeyName'), 39],
            [sprintf(self::BEHAVIOUR, 'revoke'), 65],
            [sprintf(self::BEHAVIOUR, 'scopeExpired'), 70],
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
        return $this->hooks === null ? new DisallowModelBehaviourRule : new DisallowModelBehaviourRule($this->hooks);
    }
}
