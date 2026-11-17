<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireFormRequestRulesRule;

/**
 * Tests for the form request rules() rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireFormRequestRulesRule>
 *
 * @internal
 */
#[CoversClass(RequireFormRequestRulesRule::class)]
final class RequireFormRequestRulesRuleTest extends RuleTestCase
{
    /** @var string The missing rules message. */
    private const string MESSAGE = 'A form request must define a rules() method.';

    /**
     * Flags a concrete form request without a public rules().
     *
     * @return void
     */
    public function testFlagsFormRequestWithoutRules(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-rules.inc'], [
            [self::MESSAGE, 15],
            [self::MESSAGE, 39],
            [self::MESSAGE, 59],
            [self::MESSAGE, 63],
        ]);
    }

    /**
     * Recognises a form request by its ancestry, wherever it lives.
     *
     * @return void
     */
    public function testRecognisesAFormRequestByItsAncestry(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-outside.inc'], [
            [self::MESSAGE, 9],
        ]);
    }

    /**
     * Ignores other classes under an Http\Requests namespace.
     *
     * @return void
     */
    public function testIgnoresOtherClassesBesideTheRequests(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-rule-classes.inc'], []);
    }

    /**
     * Falls back to a parent written as FormRequest when the ancestry fails.
     *
     * @return void
     */
    public function testFallsBackToTheParentNameWhenTheAncestryDoesNotResolve(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-unresolved.inc'], [
            [self::MESSAGE, 8],
        ]);
    }

    /**
     * A class declared under tests/ is never flagged.
     *
     * @return void
     */
    public function testIgnoresClassesDeclaredInTests(): void
    {
        $this->analyse([__DIR__ . '/data/tests/form-request-tests.inc'], []);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PHPStan\Node\InClassNode>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new RequireFormRequestRulesRule;
    }
}
