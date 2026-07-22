<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireSnakeCaseSchemaRule;

/**
 * Tests for the snake_case schema naming rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireSnakeCaseSchemaRule>
 *
 * @internal
 */
#[CoversClass(RequireSnakeCaseSchemaRule::class)]
final class RequireSnakeCaseSchemaRuleTest extends RuleTestCase
{
    /**
     * Non-snake_case table and column names are flagged inside a migration;
     * snake_case names, value arguments, and code outside a migration are not.
     *
     * @return void
     */
    public function testFlagsNonSnakeCaseNames(): void
    {
        $this->analyse([__DIR__ . '/data/snake-case-schema.inc'], [
            ['Table name "blogPosts" must use snake_case.', 20],
            ['Column name "firstName" must use snake_case.', 23],
            ['Column name "authorId" must use snake_case.', 25],
            ['Column name "authorId" must use snake_case.', 29],
            ['Column name "BadOne" must use snake_case.', 30],
            ['Column name "BadTwo" must use snake_case.', 30],
            ['Column name "BadUnique" must use snake_case.', 34],
            ['Column name "BadIndex" must use snake_case.', 35],
            ['Table name "OldName" must use snake_case.', 38],
            ['Table name "NewThings" must use snake_case.', 38],
            ['Table name "blogPosts" must use snake_case.', 50],
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
        return new RequireSnakeCaseSchemaRule;
    }
}
