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
    /**
     * A concrete form request without rules() is flagged; one with rules(), an
     * abstract base request and a class outside Http\Requests are not.
     *
     * @return void
     */
    public function testFlagsFormRequestWithoutRules(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-rules.inc'], [
            [
                'A form request must define a rules() method.',
                13,
            ],
        ]);
    }

    /**
     * A class outside an Http\Requests namespace is never flagged.
     *
     * @return void
     */
    public function testIgnoresClassesOutsideRequestsNamespace(): void
    {
        $this->analyse([__DIR__ . '/data/form-request-outside.inc'], []);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new RequireFormRequestRulesRule;
    }
}
