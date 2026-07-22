<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
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
#[CoversClass(RequireMigrationMethodsRule::class)]
final class RequireMigrationMethodsRuleTest extends RuleTestCase
{
    /** @var string The expected missing-down() error message. */
    private const string DOWN_ERROR = 'A migration must define the down() method.';

    /** @var string The expected missing-up() error message. */
    private const string UP_ERROR = 'A migration must define the up() method.';

    /**
     * A migration missing up() or down() is flagged (an empty one for both); a
     * complete migration, one whose method names differ only by case, and a
     * non-migration are not.
     *
     * @return void
     */
    public function testFlagsMigrationsMissingMethods(): void
    {
        $this->analyse([__DIR__ . '/data/migration-methods.inc'], [
            [
                self::DOWN_ERROR,
                18,
            ],
            [
                self::UP_ERROR,
                25,
            ],
            [
                self::UP_ERROR,
                50,
            ],
            [
                self::DOWN_ERROR,
                50,
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
                self::DOWN_ERROR,
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
