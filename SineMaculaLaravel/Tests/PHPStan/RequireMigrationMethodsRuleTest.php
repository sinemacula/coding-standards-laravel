<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireMigrationMethodsRule;

/**
 * Tests for the migration methods rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireMigrationMethodsRule>
 *
 * @internal
 */
#[CoversNothing]
final class RequireMigrationMethodsRuleTest extends RuleTestCase
{
    /**
     * A migration missing up() or down() is flagged; a complete migration and
     * a non-migration are not.
     *
     * @return void
     */
    public function testFlagsMigrationsMissingMethods(): void
    {
        $this->analyse([__DIR__ . '/data/migration-methods.inc'], [
            [
                'A migration must define the down() method.',
                18,
            ],
            [
                'A migration must define the up() method.',
                25,
            ],
        ]);
    }

    /**
     * The anonymous class a migration file returns is checked the same way.
     *
     * @return void
     */
    public function testFlagsAnonymousMigration(): void
    {
        $this->analyse([__DIR__ . '/data/migration-anonymous.inc'], [
            [
                'A migration must define the down() method.',
                5,
            ],
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
        return new RequireMigrationMethodsRule;
    }
}
