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
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Disallow defining timestamps in a factory definition().
 *
 * Eloquent sets created_at and updated_at automatically, so a factory must not
 * hard-set them in `definition()`. Only the array returned directly from the
 * method is inspected; classes that do not extend Factory are unaffected.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class DisallowFactoryTimestampsRule implements Rule
{
    /** @var array<int, string> The array keys a factory definition must not set. */
    private const array FORBIDDEN = ['created_at', 'updated_at'];

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
     * Flag timestamp keys in a factory's definition() array.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->extends?->getLast() !== 'Factory') {
            return [];
        }

        $definition = $this->definitionMethod($node);

        if ($definition === null) {
            return [];
        }

        return $this->timestampErrors($definition);
    }

    /**
     * Find the class's definition() method, if any.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return \PhpParser\Node\Stmt\ClassMethod|null
     */
    private function definitionMethod(Class_ $node): ?ClassMethod
    {
        foreach ($node->getMethods() as $method) {
            if ($method->name->toString() === 'definition') {
                return $method;
            }
        }

        return null;
    }

    /**
     * Collect errors for forbidden keys in the method's returned array.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function timestampErrors(ClassMethod $method): array
    {
        foreach ($method->stmts ?? [] as $stmt) {
            if ($stmt instanceof Return_ && $stmt->expr instanceof Array_) {
                return $this->arrayKeyErrors($stmt->expr);
            }
        }

        return [];
    }

    /**
     * Build an error for each forbidden string key in the array.
     *
     * @param  \PhpParser\Node\Expr\Array_  $array
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function arrayKeyErrors(Array_ $array): array
    {
        $errors = [];

        foreach ($array->items as $item) {
            $key = $item->key;

            if (!$key instanceof String_ || !in_array($key->value, self::FORBIDDEN, true)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'A factory definition() must not set %s; Eloquent manages timestamps.',
                $key->value,
            ))->identifier('sineMaculaLaravel.factoryTimestamps')->line($item->getStartLine())->build();
        }

        return $errors;
    }
}
