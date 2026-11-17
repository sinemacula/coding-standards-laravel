<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireMassAssignmentDeclarationRule;

/**
 * Tests for the mass-assignment declaration rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireMassAssignmentDeclarationRule>
 *
 * @internal
 */
#[CoversClass(RequireMassAssignmentDeclarationRule::class)]
final class RequireMassAssignmentDeclarationRuleTest extends RuleTestCase
{
    /**
     * Flags a concrete model declaring neither mass-assignment form.
     *
     * @return void
     */
    public function testFlagsModelsWithoutMassAssignment(): void
    {
        $this->analyse([__DIR__ . '/data/mass-assignment.inc'], [
            [
                'Model must declare mass assignment explicitly via $fillable or $guarded.',
                8,
            ],
        ]);
    }

    /**
     * Exempts models declared in a test file.
     *
     * @return void
     */
    public function testExemptsModelsDeclaredInTests(): void
    {
        $this->analyse([__DIR__ . '/data/tests/mass-assignment-tests.inc'], []);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new RequireMassAssignmentDeclarationRule;
    }
}
