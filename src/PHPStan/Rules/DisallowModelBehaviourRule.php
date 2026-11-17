<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassMethod as DeclaredMethod;
use PHPStan\Node\ClassMethodsNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsRelationships;

/**
 * Limit an Eloquent model to its schema and its relations.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\ClassMethodsNode>
 */
final readonly class DisallowModelBehaviourRule implements Rule
{
    use DetectsRelationships;

    /** @var array<string, string> Base classes whose descendants are models, by name and by short name. */
    private const array MODEL_BASE_CLASSES = [
        'Illuminate\Database\Eloquent\Model'                => 'Model',
        'Illuminate\Foundation\Auth\User'                   => 'Authenticatable',
        'Illuminate\Database\Eloquent\Relations\Pivot'      => 'Pivot',
        'Illuminate\Database\Eloquent\Relations\MorphPivot' => 'MorphPivot',
    ];

    /** @var string Return type marking an accessor or mutator. */
    private const string ATTRIBUTE_RETURN = 'Attribute';

    /** @var string Attribute marking a query scope. */
    private const string SCOPE_ATTRIBUTE = 'Scope';

    /** @var string Clause naming where a method reported against the class line actually comes from. */
    private const string IMPORTED = ', imported from a trait,';

    /** @var array<int, string> Framework hooks a model may declare. */
    private array $hooks;

    /**
     * @param  array<int, string>  $hooks
     */
    public function __construct(
        array $hooks = [
            'uniqueIds', 'newUniqueId', 'getRouteKeyName', 'resolveRouteBindingQuery', 'getMorphClass', 'prunable',
            'getTable', 'getConnectionName',
        ],
    ) {
        $this->hooks = $hooks;
    }

    /**
     * The node type this rule inspects.
     *
     * @return string
     */
    #[\Override]
    public function getNodeType(): string
    {
        return ClassMethodsNode::class;
    }

    /**
     * Flag the members of a model that are neither schema nor relations.
     *
     * @param  \PHPStan\Node\ClassMethodsNode  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $origin = $node->getClass();
        $class  = $node->getClassReflection();

        if ($origin instanceof Class_ === false) {
            return [];
        }

        $bases = $this->modelBases($class);

        if ($bases === [] && $this->extendsModelBaseByName($origin) === false) {
            return [];
        }

        $errors = [];

        foreach ($node->getMethods() as $declared) {
            $error = $this->methodError($class, $bases, $origin, $declared);

            if ($error === null) {
                continue;
            }

            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * The framework bases the class resolves an ancestry to.
     *
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @return array<int, \PHPStan\Reflection\ClassReflection>
     */
    private function modelBases(ClassReflection $class): array
    {
        $ancestors = array_map(
            static fn (string $name): ?ClassReflection => $class->getAncestorWithClassName($name),
            array_keys(self::MODEL_BASE_CLASSES),
        );

        return array_filter($ancestors);
    }

    /**
     * Whether the parent as written names one of the framework bases.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $origin
     * @return bool
     */
    private function extendsModelBaseByName(Class_ $origin): bool
    {
        return in_array((string) $origin->extends?->getLast(), self::MODEL_BASE_CLASSES, true);
    }

    /**
     * Build the error for a method that does not belong on a model, if it is
     * one.
     *
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @param  array<int, \PHPStan\Reflection\ClassReflection>  $bases
     * @param  \PhpParser\Node\Stmt\Class_  $origin
     * @param  \PHPStan\Node\ClassMethod  $declared
     * @return \PHPStan\Rules\RuleError|null
     */
    private function methodError(ClassReflection $class, array $bases, Class_ $origin, DeclaredMethod $declared): ?RuleError
    {
        $method = $declared->getNode();
        $name   = $method->name->toString();

        $imported = $declared->isDeclaredInTrait() ? self::IMPORTED : '';
        $line     = $declared->isDeclaredInTrait() ? $origin->getStartLine() : $method->getStartLine();

        if ($this->isScope($method)) {
            return $this->error(sprintf(
                'Query scope "%s()"%s belongs on a repository, which can compose it as a fluent method, '
                . 'not on the model.',
                $name,
                $imported,
            ), $line);
        }

        if ($method->isPublic() === false || $this->isPermitted($class, $bases, $method, $name)) {
            return null;
        }

        return $this->error(sprintf(
            'Method "%s()"%s puts behaviour on a model; move it to a repository or a service. A model '
            . 'carries its schema and its relations.',
            $name,
            $imported,
        ), $line);
    }

    /**
     * Whether the method is one a model may declare.
     *
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @param  array<int, \PHPStan\Reflection\ClassReflection>  $bases
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @param  string  $name
     * @return bool
     */
    private function isPermitted(ClassReflection $class, array $bases, ClassMethod $method, string $name): bool
    {
        return in_array($name, $this->hooks, true)
            || $this->isRequiredByContract($class, $bases, $name)
            || $this->hasRelationshipReturn($method)
            || $this->returnsAttribute($method);
    }

    /**
     * Whether an interface the model's own author opted into declares the
     * method.
     *
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @param  array<int, \PHPStan\Reflection\ClassReflection>  $bases
     * @param  string  $name
     * @return bool
     */
    private function isRequiredByContract(ClassReflection $class, array $bases, string $name): bool
    {
        foreach ($class->getInterfaces() as $interface) {
            if ($this->isCarriedByBase($bases, $interface) === false && $interface->hasMethod($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether one of the framework bases already implements the interface.
     *
     * @param  array<int, \PHPStan\Reflection\ClassReflection>  $bases
     * @param  \PHPStan\Reflection\ClassReflection  $interface
     * @return bool
     */
    private function isCarriedByBase(array $bases, ClassReflection $interface): bool
    {
        foreach ($bases as $base) {
            if ($base->implementsInterface($interface->getName())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the method is an accessor or mutator, declared by its return type
     * rather than by name.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @return bool
     */
    private function returnsAttribute(ClassMethod $method): bool
    {
        $type = $method->returnType;

        return $type instanceof Node\Name && $type->getLast() === self::ATTRIBUTE_RETURN;
    }

    /**
     * Whether the method carries the query scope attribute.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @return bool
     */
    private function isScope(ClassMethod $method): bool
    {
        foreach ($method->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                if ($attribute->name->getLast() === self::SCOPE_ATTRIBUTE) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Build a rule error against a line of the class.
     *
     * @param  string  $message
     * @param  int  $line
     * @return \PHPStan\Rules\RuleError
     */
    private function error(string $message, int $line): RuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('sineMaculaLaravel.modelBehaviour')
            ->line($line)
            ->build();
    }
}
