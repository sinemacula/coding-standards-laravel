<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsRelationships;

/**
 * Require a return type on Eloquent relationship methods.
 *
 * A method that returns a relationship (`return $this->hasMany(...)`, etc.)
 * without a declared return type hides the relationship type from IDEs and
 * static analysis. Any method whose returned expression resolves to a
 * `$this-><relationship>()` call must declare its return type.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\ClassMethod>
 */
final class RequireRelationshipReturnTypeRule implements Rule
{
    use DetectsRelationships;

    /**
     * The node type this rule inspects.
     *
     * @return string
     */
    #[\Override]
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * Flag a relationship method that does not declare a return type.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->returnType !== null || $this->hasRelationshipReturn($node) === false) {
            return [];
        }

        return [RuleErrorBuilder::message(sprintf(
            'Relationship method "%s()" must declare its return type.',
            $node->name->toString(),
        ))->identifier('sineMaculaLaravel.relationshipReturnType')->build()];
    }
}
