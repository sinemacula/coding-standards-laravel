<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireSnakeCaseResourceFieldsRule;

/**
 * Tests for the snake_case resource field naming rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireSnakeCaseResourceFieldsRule>
 *
 * @internal
 */
#[CoversClass(RequireSnakeCaseResourceFieldsRule::class)]
final class RequireSnakeCaseResourceFieldsRuleTest extends RuleTestCase
{
    /**
     * Non-snake_case keys in a resource's toArray() result are flagged, nested
     * arrays included; snake_case keys, list values, a spread, a non-literal
     * return and non-resource classes are not.
     *
     * @return void
     */
    public function testFlagsNonSnakeCaseFieldKeys(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-resource-fields.inc'], [
            ['Resource field "createdAt" must use snake_case.', 14],
            ['Resource field "postCode" must use snake_case.', 17],
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
        return new RequireSnakeCaseResourceFieldsRule;
    }
}
