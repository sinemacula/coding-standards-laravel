<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbid the Eloquent $casts property.
 *
 * Laravel 11+ declares attribute casts through the `casts()` method, which can
 * call into other casts and merges cleanly with a parent's casts. The `$casts`
 * property is flagged so casts are always declared via `casts()` - but only on
 * an Eloquent model (a class extending `Model`, `Authenticatable` or `Pivot`),
 * so a non-model class with its own `$casts` (e.g. a cache) is left alone.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class DisallowCastsPropertyRule implements Rule
{
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
     * Flag a $casts property declared on an Eloquent model.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (in_array($node->extends?->getLast(), self::MODEL_BASE_CLASSES, true) === false) {
            return [];
        }

        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $item) {
                if ($item->name->toString() === 'casts') {
                    return [RuleErrorBuilder::message(
                        'The $casts property is not allowed; declare casts via the casts() method instead.',
                    )->identifier('sineMaculaLaravel.castsProperty')->line($item->getStartLine())->build()];
                }
            }
        }

        return [];
    }
}
