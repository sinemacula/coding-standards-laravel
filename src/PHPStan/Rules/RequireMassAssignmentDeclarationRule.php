<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Require an explicit mass-assignment declaration on Eloquent models.
 *
 * A model that declares neither `$fillable` nor `$guarded` inherits Eloquent's
 * default of guarding nothing, which silently exposes every column to mass
 * assignment. Each concrete model that extends `Model` must declare one of the
 * two. Abstract base models and non-models (e.g. a pivot extending `Pivot`) are
 * not affected.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class RequireMassAssignmentDeclarationRule implements Rule
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
     * Flag a concrete model that declares neither $fillable nor $guarded.
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
            || $node->extends?->getLast() !== 'Model'
            || $this->hasMassAssignmentDeclaration($node)
        ) {
            return [];
        }

        return [RuleErrorBuilder::message(
            'Model must declare mass assignment explicitly via $fillable or $guarded.',
        )->identifier('sineMaculaLaravel.massAssignment')->build()];
    }

    /**
     * Determine whether the class declares a $fillable or $guarded property.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return bool
     */
    private function hasMassAssignmentDeclaration(Class_ $node): bool
    {
        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $item) {
                if (in_array($item->name->toString(), ['fillable', 'guarded'], true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
