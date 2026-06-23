<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Require snake_case field names in API resources.
 *
 * A resource's output keys are part of the public API contract and are
 * snake_case by Laravel convention. On a class extending JsonResource this
 * flags non-snake_case string-literal keys in the array returned directly from
 * toArray(), recursing into nested array literals. Computed keys, a toArray()
 * that does not return an array literal, and non-resource classes are left
 * alone. Digits are permitted (line_1) and leading-underscore keys are exempt
 * meta-fields (e.g. _type); only casing is enforced on data fields.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class RequireSnakeCaseResourceFieldsRule implements Rule
{
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
     * Flag non-snake_case field keys in a resource's toArray() result.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->extends?->getLast() !== 'JsonResource') {
            return [];
        }

        $method = $this->toArrayMethod($node);
        $array  = $method === null ? null : $this->returnedArray($method);

        return $array === null ? [] : $this->keyErrors($array);
    }

    /**
     * Find the class's toArray() method, if any.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return \PhpParser\Node\Stmt\ClassMethod|null
     */
    private function toArrayMethod(Class_ $node): ?ClassMethod
    {
        foreach ($node->getMethods() as $method) {
            if ($method->name->toString() === 'toArray') {
                return $method;
            }
        }

        return null;
    }

    /**
     * The array literal returned directly from the method, if any.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @return \PhpParser\Node\Expr\Array_|null
     */
    private function returnedArray(ClassMethod $method): ?Array_
    {
        foreach ($method->stmts ?? [] as $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr instanceof Array_) {
                return $stmt->expr;
            }
        }

        return null;
    }

    /**
     * Collect errors for non-snake_case keys, recursing into nested arrays.
     *
     * @param  \PhpParser\Node\Expr\Array_  $array
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function keyErrors(Array_ $array): array
    {
        $errors = [];

        foreach ($array->items as $item) {
            // Leading-underscore keys (e.g. _type) are exempt meta-fields.
            if (
                $item->key instanceof String_
                && !str_starts_with($item->key->value, '_')
                && !$this->isSnakeCase($item->key->value)
            ) {
                $errors[] = $this->keyError($item->key);
            }

            if (!$item->value instanceof Array_) {
                continue;
            }

            $errors = array_merge($errors, $this->keyErrors($item->value));
        }

        return $errors;
    }

    /**
     * Build the error for a single non-snake_case field key.
     *
     * @param  \PhpParser\Node\Scalar\String_  $key
     * @return \PHPStan\Rules\RuleError
     */
    private function keyError(String_ $key): RuleError
    {
        return RuleErrorBuilder::message(sprintf(
            'Resource field "%s" must use snake_case.',
            $key->value,
        ))->identifier('sineMaculaLaravel.resourceFieldNaming')->line($key->getStartLine())->build();
    }

    /**
     * Whether a name is lower snake_case, optionally with digits.
     *
     * @param  string  $value
     * @return bool
     */
    private function isSnakeCase(string $value): bool
    {
        return preg_match('/^[a-z][a-z0-9]*(_[a-z0-9]+)*$/', $value) === 1;
    }
}
