<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsRelationships;

/**
 * Limit an Eloquent model to its schema and its relations.
 *
 * A model describes what a record is; behaviour belongs to the layer that owns
 * it, a repository or a service. A model that accumulates behaviour is the
 * usual way a codebase stops being navigable, so a public method that is not a
 * relation, a framework hook or an accessor is reported.
 *
 * A query scope is called out separately, because the framework invites it and
 * it is where the erosion tends to start: it belongs on a repository, which can
 * compose it as an ordinary fluent method. A scope is reported whatever its
 * visibility, since the attribute form is conventionally not public.
 *
 * Non-public methods are left alone - a helper called only from a relation is
 * detail, whereas a public one is what other layers reach for. The permitted
 * hooks are the configurable `modelHooks` parameter, since the framework adds
 * to them over time and a project's set depends on the version it pins.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final readonly class DisallowModelBehaviourRule implements Rule
{
    use DetectsRelationships;

    /** @var array<int, string> Base classes whose subclasses are models. */
    private const array MODEL_BASE_CLASSES = ['Model', 'Authenticatable', 'Pivot'];

    /** @var string Return type marking an accessor or mutator. */
    private const string ATTRIBUTE_RETURN = 'Attribute';

    /** @var string Attribute marking a query scope. */
    private const string SCOPE_ATTRIBUTE = 'Scope';

    /** @var array<int, string> Framework hooks a model may declare. */
    private array $hooks;

    /**
     * @param  array<int, string>  $hooks
     */
    public function __construct(array $hooks = ['casts', 'booted', 'boot', 'newFactory', 'getRouteKeyName', 'uniqueIds'])
    {
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
        return Class_::class;
    }

    /**
     * Flag the members of a model that are neither schema nor relations.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (in_array((string) $node->extends?->getLast(), self::MODEL_BASE_CLASSES, true) === false) {
            return [];
        }

        $errors = [];

        foreach ($node->getMethods() as $method) {
            $error = $this->methodError($method);

            if ($error === null) {
                continue;
            }

            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Build the error for a method that does not belong on a model, if it is
     * one.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @return \PHPStan\Rules\RuleError|null
     */
    private function methodError(ClassMethod $method): ?RuleError
    {
        $name = $method->name->toString();

        if ($this->isScope($method)) {
            return $this->error(sprintf(
                'Query scope "%s()" belongs on a repository, which can compose it as a fluent method, '
                . 'not on the model.',
                $name,
            ), $method);
        }

        if ($method->isPublic() === false || $this->isPermitted($method, $name)) {
            return null;
        }

        return $this->error(sprintf(
            'Method "%s()" puts behaviour on a model; move it to a repository or a service. A model '
            . 'carries its schema and its relations.',
            $name,
        ), $method);
    }

    /**
     * Whether the method is one a model may declare.
     *
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @param  string  $name
     * @return bool
     */
    private function isPermitted(ClassMethod $method, string $name): bool
    {
        return in_array($name, $this->hooks, true)
            || $this->hasRelationshipReturn($method)
            || $this->returnsAttribute($method);
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
     * Build a rule error against a method.
     *
     * @param  string  $message
     * @param  \PhpParser\Node\Stmt\ClassMethod  $method
     * @return \PHPStan\Rules\RuleError
     */
    private function error(string $message, ClassMethod $method): RuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier('sineMaculaLaravel.modelBehaviour')
            ->line($method->getStartLine())
            ->build();
    }
}
