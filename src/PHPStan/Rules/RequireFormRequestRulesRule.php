<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsTestFiles;

/**
 * Require form requests to define a rules() method.
 *
 * A concrete class descending from the framework's `FormRequest` must have the
 * validation rules it enforces, whether it declares `rules()` itself or
 * inherits it from a base request of the project's own, as long as the method
 * is public, since the framework calls it from outside the class. A form
 * request is recognised by its ancestry rather than where it lives, so a
 * validation rule or any other class kept beside the requests is not mistaken
 * for one. Where the ancestry does not resolve, a parent written as
 * `FormRequest` still marks one. Abstract base requests and classes declared in
 * tests are not affected.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\InClassNode>
 */
final class RequireFormRequestRulesRule implements Rule
{
    use DetectsTestFiles;

    /** @var string The framework base every form request descends from. */
    private const string FORM_REQUEST = 'Illuminate\Foundation\Http\FormRequest';

    /** @var string The framework base's short name, read when the ancestry does not resolve. */
    private const string FORM_REQUEST_SHORT_NAME = 'FormRequest';

    /**
     * The node type this rule inspects.
     *
     * @return string
     */
    #[\Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    /**
     * Flag a concrete form request that has no rules() method.
     *
     * @param  \PHPStan\Node\InClassNode  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $origin = $node->getOriginalNode();
        $class  = $node->getClassReflection();

        if (
            !$origin instanceof Class_
            || $origin->isAbstract()
            || $this->isTestFile($scope)
            || !$this->isFormRequest($class, $origin)
            || $this->hasCallableRules($class)
        ) {
            return [];
        }

        return [RuleErrorBuilder::message(
            'A form request must define a rules() method.',
        )->identifier('sineMaculaLaravel.formRequestRules')->build()];
    }

    /**
     * Whether the class has a public rules() method, its own or inherited, as
     * the framework calls it from outside the class.
     *
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @return bool
     */
    private function hasCallableRules(ClassReflection $class): bool
    {
        return $class->hasNativeMethod('rules') && $class->getNativeMethod('rules')->isPublic();
    }

    /**
     * Whether the class is a form request: it descends from the framework base,
     * or its ancestry does not resolve and its parent is written as one.
     *
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @param  \PhpParser\Node\Stmt\Class_  $origin
     * @return bool
     */
    private function isFormRequest(ClassReflection $class, Class_ $origin): bool
    {
        if ($class->is(self::FORM_REQUEST)) {
            return true;
        }

        return $class->getParentClass() === null && $origin->extends?->getLast() === self::FORM_REQUEST_SHORT_NAME;
    }
}
