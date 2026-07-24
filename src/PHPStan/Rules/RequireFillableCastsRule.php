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
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsTestFiles;

/**
 * Require every mass-assignable attribute to declare a cast.
 *
 * A cast is the single in-code declaration of an attribute's type, so every
 * attribute a model exposes to mass assignment must carry one, documenting the
 * shape of each settable field. For each `$fillable` entry (or the
 * `#[Fillable]` attribute form) this requires a matching key in the `casts()`
 * method (or a `$casts` property). It runs only on a concrete model that
 * directly extends `Model`, `Authenticatable` or `Pivot`, so a subclass whose
 * parent declares the casts is left alone; models declared in tests are exempt.
 *
 * The fillable list and the cast set must both be statically enumerable. When
 * either is assembled dynamically - a `$fillable` built from a variable, or a
 * `casts()` that merges `parent::casts()` - the model is skipped rather than
 * risk a false positive against keys the rule cannot see.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class RequireFillableCastsRule implements Rule
{
    use DetectsTestFiles;

    /** @var array<int, string> Eloquent model base classes. */
    private const array MODEL_BASE_CLASSES = ['Model', 'Authenticatable', 'Pivot'];

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
     * Flag each fillable attribute of a model that declares no cast.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (
            $node->isAbstract()
            || in_array($node->extends?->getLast(), self::MODEL_BASE_CLASSES, true) === false
            || $this->isTestFile($scope)
        ) {
            return [];
        }

        $fillable = $this->fillableAttributes($node);
        $casts    = $fillable === null || $fillable === [] ? null : $this->castKeys($node);

        if ($fillable === null || $fillable === [] || $casts === null) {
            return [];
        }

        return $this->missingCastErrors($fillable, $casts);
    }

    /**
     * Build an error for each fillable attribute absent from the cast set.
     *
     * @param  array<int, \PhpParser\Node\Scalar\String_>  $fillable
     * @param  array<int, string>  $casts
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function missingCastErrors(array $fillable, array $casts): array
    {
        $errors = [];

        foreach ($fillable as $attribute) {
            if (in_array($attribute->value, $casts, true)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Fillable attribute "%s" must declare a cast in the casts() method.',
                $attribute->value,
            ))->identifier('sineMaculaLaravel.fillableCasts')->line($attribute->getStartLine())->build();
        }

        return $errors;
    }

    /**
     * Resolve the model's fillable attributes as string literals.
     *
     * Returns null when a `$fillable` or `#[Fillable]` declaration is present
     * but not a plain array of string literals, so the caller skips the model.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return array<int, \PhpParser\Node\Scalar\String_>|null
     */
    private function fillableAttributes(Class_ $node): ?array
    {
        $property  = $this->fillablePropertyArray($node);
        $attribute = $this->fillableAttributeArray($node);

        if ($property === false || $attribute === false) {
            return null;
        }

        return $this->arrayStrings(array_filter([$property, $attribute]));
    }

    /**
     * The array assigned to a `$fillable` property: null when absent, false
     * when present but not an array literal.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return false|\PhpParser\Node\Expr\Array_|null
     */
    private function fillablePropertyArray(Class_ $node): Array_|false|null
    {
        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $item) {
                if ($item->name->toString() !== 'fillable') {
                    continue;
                }

                return $item->default instanceof Array_ ? $item->default : false;
            }
        }

        return null;
    }

    /**
     * The array passed to a `#[Fillable]` attribute: null when absent, false
     * when present but not a single array-literal argument.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return false|\PhpParser\Node\Expr\Array_|null
     */
    private function fillableAttributeArray(Class_ $node): Array_|false|null
    {
        foreach ($node->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                if ($attribute->name->getLast() !== 'Fillable') {
                    continue;
                }

                $argument = $attribute->args[0]->value ?? null;

                return $argument instanceof Array_ ? $argument : false;
            }
        }

        return null;
    }

    /**
     * The cast keys a model declares via `casts()` or a `$casts` property.
     *
     * Returns null when a declaration is present but not statically enumerable,
     * so the caller skips the model rather than flag keys it cannot see.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return array<int, string>|null
     */
    private function castKeys(Class_ $node): ?array
    {
        $method   = $this->castsMethodArray($node);
        $property = $this->castsPropertyArray($node);

        if ($method === false || $property === false) {
            return null;
        }

        $keys = [];

        foreach (array_filter([$method, $property]) as $array) {
            foreach ($array->items as $item) {
                if (!$item->key instanceof String_) {
                    return null;
                }

                $keys[] = $item->key->value;
            }
        }

        return $keys;
    }

    /**
     * The array returned by a `casts()` method: null when absent, false when
     * the body is anything other than a single return of an array literal.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return false|\PhpParser\Node\Expr\Array_|null
     */
    private function castsMethodArray(Class_ $node): Array_|false|null
    {
        $method = $this->findMethod($node, 'casts');

        if ($method === null) {
            return null;
        }

        $statements = $method->stmts ?? [];

        if (count($statements) !== 1 || !$statements[0] instanceof Return_) {
            return false;
        }

        return $statements[0]->expr instanceof Array_ ? $statements[0]->expr : false;
    }

    /**
     * The array assigned to a `$casts` property: null when absent, false when
     * present but not an array literal.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return false|\PhpParser\Node\Expr\Array_|null
     */
    private function castsPropertyArray(Class_ $node): Array_|false|null
    {
        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $item) {
                if ($item->name->toString() !== 'casts') {
                    continue;
                }

                return $item->default instanceof Array_ ? $item->default : false;
            }
        }

        return null;
    }

    /**
     * Find a class method by name, case-insensitively.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  string  $name
     * @return \PhpParser\Node\Stmt\ClassMethod|null
     */
    private function findMethod(Class_ $node, string $name): ?ClassMethod
    {
        foreach ($node->getMethods() as $method) {
            if (strtolower($method->name->toString()) === $name) {
                return $method;
            }
        }

        return null;
    }

    /**
     * The string-literal items across the given array literals, in order.
     *
     * @param  array<int, \PhpParser\Node\Expr\Array_>  $arrays
     * @return array<int, \PhpParser\Node\Scalar\String_>
     */
    private function arrayStrings(array $arrays): array
    {
        $strings = [];

        foreach ($arrays as $array) {
            foreach ($array->items as $item) {
                if (!$item->value instanceof String_) {
                    continue;
                }

                $strings[] = $item->value;
            }
        }

        return $strings;
    }
}
