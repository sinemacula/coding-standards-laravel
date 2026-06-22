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
 * assignment. Each concrete model that extends `Model` must declare one - via
 * the `$fillable`/`$guarded` property or the 13.2+ `#[Fillable]`/`#[Guarded]`
 * attribute. Abstract base models, non-models (e.g. a pivot extending `Pivot`),
 * and models declared in tests (not attack surface) are not affected.
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
     * Flag a concrete production model that declares no mass-assignment intent.
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
            || $this->isTestFile($scope)
            || $this->hasMassAssignmentDeclaration($node)
        ) {
            return [];
        }

        return [RuleErrorBuilder::message(
            'Model must declare mass assignment explicitly via $fillable or $guarded.',
        )->identifier('sineMaculaLaravel.massAssignment')->build()];
    }

    /**
     * Whether the analysed file lives under a tests/ directory.
     *
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function isTestFile(Scope $scope): bool
    {
        return str_contains(str_replace('\\', '/', $scope->getFile()), '/tests/');
    }

    /**
     * Whether the class declares mass assignment as a property or attribute.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return bool
     */
    private function hasMassAssignmentDeclaration(Class_ $node): bool
    {
        return $this->hasMassAssignmentProperty($node) || $this->hasMassAssignmentAttribute($node);
    }

    /**
     * Whether the class declares a $fillable or $guarded property.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return bool
     */
    private function hasMassAssignmentProperty(Class_ $node): bool
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

    /**
     * Whether the class carries a #[Fillable] or #[Guarded] attribute.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return bool
     */
    private function hasMassAssignmentAttribute(Class_ $node): bool
    {
        foreach ($node->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                if (in_array($attribute->name->getLast(), ['Fillable', 'Guarded'], true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
