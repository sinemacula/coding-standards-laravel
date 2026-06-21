<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Require form requests to define a rules() method.
 *
 * A concrete class under an `Http\Requests` namespace is a form request and
 * must declare the validation rules it enforces. Abstract base requests and
 * classes outside that namespace are not affected.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class RequireFormRequestRulesRule implements Rule
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
     * Flag a concrete form request that declares no rules() method.
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
            || !$this->isInRequestsNamespace($scope)
            || $this->hasRulesMethod($node)
        ) {
            return [];
        }

        return [RuleErrorBuilder::message(
            'A form request must define a rules() method.',
        )->identifier('sineMaculaLaravel.formRequestRules')->build()];
    }

    /**
     * Determine whether the scope sits in an Http\Requests namespace.
     *
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function isInRequestsNamespace(Scope $scope): bool
    {
        return str_contains('\\' . ($scope->getNamespace() ?? '') . '\\', '\Http\Requests\\');
    }

    /**
     * Determine whether the class declares a rules() method.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return bool
     */
    private function hasRulesMethod(Class_ $node): bool
    {
        foreach ($node->getMethods() as $method) {
            if (strtolower($method->name->toString()) === 'rules') {
                return true;
            }
        }

        return false;
    }
}
