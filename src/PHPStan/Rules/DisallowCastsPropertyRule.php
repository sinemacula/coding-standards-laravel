<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbid the Eloquent $casts property.
 *
 * Laravel 11+ declares attribute casts through the `casts()` method, which can
 * call into other casts and merges cleanly with a parent's casts. The `$casts`
 * property is flagged so casts are always declared via `casts()`.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Property>
 */
final class DisallowCastsPropertyRule implements Rule
{
    /**
     * The node type this rule inspects.
     *
     * @return string
     */
    #[\Override]
    public function getNodeType(): string
    {
        return Property::class;
    }

    /**
     * Flag a property declaration named $casts.
     *
     * @param  \PhpParser\Node\Stmt\Property  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        foreach ($node->props as $property) {
            if ($property->name->toString() === 'casts') {
                return [RuleErrorBuilder::message(
                    'The $casts property is not allowed; declare casts via the casts() method instead.',
                )->identifier('sineMaculaLaravel.castsProperty')->build()];
            }
        }

        return [];
    }
}
