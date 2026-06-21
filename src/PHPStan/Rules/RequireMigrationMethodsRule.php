<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Require migrations to define both up() and down().
 *
 * A migration without down() cannot be rolled back, and one without up() does
 * nothing. Every class extending Migration - named or the anonymous form a
 * migration file returns - must declare both methods.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class RequireMigrationMethodsRule implements Rule
{
    /** @var array<int, string> The methods a migration must define. */
    private const array REQUIRED = ['up', 'down'];

    /**
     * The node type this rule inspects.
     *
     * @return string
     */
    #[\Override]
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * Flag a migration that is missing up() or down().
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->extends?->getLast() !== 'Migration') {
            return [];
        }

        $defined = $this->methodNames($node);
        $errors  = [];

        foreach (self::REQUIRED as $method) {
            if (in_array($method, $defined, true)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(
                sprintf('A migration must define the %s() method.', $method),
            )->identifier('sineMaculaLaravel.migrationMethods')->build();
        }

        return $errors;
    }

    /**
     * Collect the lower-cased method names declared on the class.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return array<int, string>
     */
    private function methodNames(Class_ $node): array
    {
        $names = [];

        foreach ($node->getMethods() as $method) {
            $names[] = strtolower($method->name->toString());
        }

        return $names;
    }
}
