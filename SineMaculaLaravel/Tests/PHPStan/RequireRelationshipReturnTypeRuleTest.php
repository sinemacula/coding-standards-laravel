<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireRelationshipReturnTypeRule;

/**
 * Tests for the relationship return type rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireRelationshipReturnTypeRule>
 *
 * @internal
 */
#[CoversClass(RequireRelationshipReturnTypeRule::class)]
final class RequireRelationshipReturnTypeRuleTest extends RuleTestCase
{
    /**
     * Relationship methods without a return type are flagged (including
     * chained ones); typed methods, non-relationship methods and relationship
     * calls on something other than $this are not.
     *
     * @return void
     */
    public function testFlagsUntypedRelationshipMethods(): void
    {
        $this->analyse([__DIR__ . '/data/relationship-return-type.inc'], [
            [
                'Relationship method "posts()" must declare its return type.',
                10,
            ],
            [
                'Relationship method "recent()" must declare its return type.',
                15,
            ],
        ]);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\ClassMethod>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new RequireRelationshipReturnTypeRule;
    }
}
