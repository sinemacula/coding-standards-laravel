<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Concerns;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;

/**
 * Recognise the methods of a model that declare a relationship.
 *
 * A relationship method is one whose returned expression resolves to a
 * `$this-><relationship>()` call, through any chain of further calls on it. The
 * rules that treat relationships differently share this so they agree on what
 * one is.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait DetectsRelationships
{
    /** @var array<int, string> The Eloquent relationship builder methods. */
    private const array RELATIONSHIP_METHODS = [
        'hasOne', 'hasMany', 'belongsTo', 'belongsToMany',
        'hasOneThrough', 'hasManyThrough',
        'morphTo', 'morphOne', 'morphMany', 'morphToMany', 'morphedByMany',
    ];

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
