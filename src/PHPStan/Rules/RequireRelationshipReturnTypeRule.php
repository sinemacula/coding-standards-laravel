<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

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
    /** @var array<int, string> The Eloquent relationship builder methods. */
    private const array RELATIONSHIP_METHODS = [
        'hasOne', 'hasMany', 'belongsTo', 'belongsToMany',
        'hasOneThrough', 'hasManyThrough',
        'morphTo', 'morphOne', 'morphMany', 'morphToMany', 'morphedByMany',
    ];

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

    /**
     * Determine whether the method returns an Eloquent relationship call.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $node
     * @return bool
     */
    private function hasRelationshipReturn(ClassMethod $node): bool
    {
        foreach ((new NodeFinder)->findInstanceOf($node->stmts ?? [], Return_::class) as $return) {
            if ($return->expr !== null && $this->isRelationshipCall($return->expr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the expression is a $this->relationship() call.
     *
     * @param  \PhpParser\Node\Expr  $expr
     * @return bool
     */
    private function isRelationshipCall(Expr $expr): bool
    {
        while ($expr instanceof MethodCall) {
            if (
                $expr->var instanceof Variable
                && $expr->var->name === 'this'
                && $expr->name instanceof Identifier
                && in_array($expr->name->toString(), self::RELATIONSHIP_METHODS, true)
            ) {
                return true;
            }

            $expr = $expr->var;
        }

        return false;
    }
}
