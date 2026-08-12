<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireForeignIdForRule;

/**
 * Tests for the foreign key declaration rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\RequireForeignIdForRule>
 *
 * @internal
 */
#[CoversClass(RequireForeignIdForRule::class)]
final class RequireForeignIdForRuleTest extends RuleTestCase
{
    /** @var string The trailing half of the expected error message. */
    private const string REASON = 'which restates the column name and key type.';

    /**
     * Each longhand foreign key column naming a model is flagged, with the
     * model derived from the column; the helper itself, an _id column declared
     * with another column method, a dynamic name, a bare "_id" and calls
     * outside a migration are not.
     *
     * @return void
     */
    public function testFlagsLonghandForeignKeyColumns(): void
    {
        $this->analyse([__DIR__ . '/data/foreign-id-for.inc'], [
            [
                'Use foreignIdFor(Organization::class) instead of foreignUuid(\'organization_id\'), '
                . self::REASON,
                16,
            ],
            [
                'Use foreignIdFor(User::class) instead of foreignId(\'user_id\'), '
                . self::REASON,
                17,
            ],
            [
                'Use foreignIdFor(AuditLogEntry::class) instead of foreignUlid(\'audit_log_entry_id\'), '
                . self::REASON,
                18,
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
        return new RequireForeignIdForRule;
    }
}
