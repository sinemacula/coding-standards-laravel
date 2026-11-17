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
            || $this->isOutOfScope($origin, $scope)
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
     * Whether the class is abstract or declared in a test.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $origin
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function isOutOfScope(Class_ $origin, Scope $scope): bool
    {
        return $origin->isAbstract() || $this->isTestFile($scope);
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
     * Whether the class descends from FormRequest, or names it as its parent
     * where the ancestry does not resolve.
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
