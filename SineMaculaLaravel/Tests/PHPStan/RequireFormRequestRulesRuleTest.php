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
     * A concrete form request without rules() is flagged, including one whose
     * abstract base declares none either. One that declares rules() in any
     * case, one that inherits it from a base request, and an abstract base are
     * not.
     *
     * @return void
     */
    public function testFlagsFormRequestWithoutRules(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-rules.inc'], [
            [self::MESSAGE, 15],
            [self::MESSAGE, 39],
        ]);
    }

    /**
     * A form request is recognised by its ancestry wherever it lives, so one
     * outside an Http\Requests namespace is still held to the rule while the
     * ordinary classes beside it are not.
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
     * A validation rule or any other class kept under an Http\Requests
     * namespace is not a form request and is left alone.
     *
     * @return void
     */
    public function testIgnoresOtherClassesBesideTheRequests(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-rule-classes.inc'], []);
    }

    /**
     * Where the ancestry does not resolve, a parent written as FormRequest
     * still marks a form request, and any other unresolved parent does not.
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
     * A class declared under a tests/ directory is never flagged, even in a
     * namespace mirroring Http\Requests.
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
