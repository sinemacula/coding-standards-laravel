<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Require foreign key columns to be declared from their model.
 *
 * `foreignIdFor(Organization::class)` derives the column name and the key type
 * from the model, so a migration says which model it points at rather than
 * restating a name and a type that have to be kept in step by hand. Inside a
 * class extending Migration this flags the longhand `foreignId`, `foreignUuid`
 * and `foreignUlid` calls whose literal column name ends in `_id`, which is
 * exactly the shape the helper produces. A dynamic name, or a column named
 * anything else, is left alone.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class RequireForeignIdForRule implements Rule
{
    /** @var array<int, string> Longhand foreign key column methods. */
    private const array FOREIGN_KEY_METHODS = ['foreignId', 'foreignUuid', 'foreignUlid'];

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
     * Flag longhand foreign key columns declared in a migration.
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

        $errors = [];

        foreach ((new NodeFinder)->findInstanceOf($node, MethodCall::class) as $call) {
            $error = $this->callError($call);

            if ($error === null) {
                continue;
            }

            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Build the error for a longhand foreign key call, if it is one.
     *
     * @param  \PhpParser\Node\Expr\MethodCall  $call
     * @return \PHPStan\Rules\RuleError|null
     */
    private function callError(MethodCall $call): ?RuleError
    {
        $method = $call->name instanceof Identifier ? $call->name->toString() : null;

        if ($method === null || in_array($method, self::FOREIGN_KEY_METHODS, true) === false) {
            return null;
        }

        $column = $this->columnName($call);

        if ($column === null) {
            return null;
        }

        return RuleErrorBuilder::message(sprintf(
            'Use foreignIdFor(%s::class) instead of %s(\'%s\'), which restates the column name and key type.',
            $this->model($column),
            $method,
            $column,
        ))->identifier('sineMaculaLaravel.foreignIdFor')->line($call->getStartLine())->build();
    }

    /**
     * The literal column name a call declares, when it ends in `_id`.
     *
     * @param  \PhpParser\Node\Expr\MethodCall  $call
     * @return string|null
     */
    private function columnName(MethodCall $call): ?string
    {
        $arg = $call->args[0] ?? null;

        if (!$arg instanceof Arg || !$arg->value instanceof String_) {
            return null;
        }

        $column = $arg->value->value;

        return str_ends_with($column, '_id') && $column !== '_id' ? $column : null;
    }

    /**
     * The model a foreign key column names, by convention.
     *
     * @param  string  $column
     * @return string
     */
    private function model(string $column): string
    {
        $base = substr($column, 0, -3);

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $base)));
    }
}
