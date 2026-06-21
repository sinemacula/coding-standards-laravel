<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Forbid the deprecated Eloquent $dates property.
 *
 * The `$dates` property was removed from Eloquent; date attributes are cast
 * through the `casts()` method (`'published_at' => 'datetime'`). This flags the
 * `$dates` property so it never reappears.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Property>
 */
final class DisallowDatesPropertyRule implements Rule
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
     * Flag a property declaration named $dates.
     *
     * @param  \PhpParser\Node\Stmt\Property  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        foreach ($node->props as $property) {
            if ($property->name->toString() === 'dates') {
                return [RuleErrorBuilder::message(
                    'The $dates property is deprecated; cast date attributes via the casts() method instead.',
                )->identifier('sineMaculaLaravel.datesProperty')->build()];
            }
        }

        return [];
    }
}
