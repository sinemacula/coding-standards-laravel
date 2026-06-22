<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Prefer model attributes over their legacy property and method forms.
 *
 * Eloquent exposes attribute classes that replace configuration properties
 * (#[Table], #[Fillable], #[Hidden]) and method overrides (#[UseFactory],
 * #[CollectedBy], #[UseEloquentBuilder]). On a model the legacy form is flagged
 * in favour of its attribute - but only for the attributes a project enables,
 * since they vary by Laravel version (the expressive set from 13.2, the
 * ObservedBy/ScopedBy/CollectedBy family from 11). The mandated set defaults to
 * #[Table]/#[Fillable]/#[Hidden] and is configurable via the
 * `sineMaculaLaravel.modelAttributes` parameter. $hidden stays a property once
 * it lists more than five fields.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class PreferModelAttributesRule implements Rule
{
    /** @var array<string, string> Known model property name => its attribute. */
    private const array PROPERTY_ATTRIBUTES = [
        'table'    => 'Table',
        'fillable' => 'Fillable',
        'hidden'   => 'Hidden',
        'touches'  => 'Touches',
    ];

    /** @var array<string, string> Known overridden model method => its attribute. */
    private const array METHOD_ATTRIBUTES = [
        'newFactory'         => 'UseFactory',
        'newCollection'      => 'CollectedBy',
        'newEloquentBuilder' => 'UseEloquentBuilder',
    ];

    /** @var int Maximum fields before the property form is preferred. */
    private const int HIDDEN_LIMIT = 5;

    /** @var array<int, string> Mandated attribute names. */
    private readonly array $attributes;

    /**
     * @param  array<int, string>  $attributes
     */
    public function __construct(array $attributes = ['Table', 'Fillable', 'Hidden'])
    {
        $this->attributes = $attributes;
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
     * Flag model members whose enabled attribute equivalent should be used.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->extends?->getLast() !== 'Model') {
            return [];
        }

        return array_merge($this->propertyErrors($node), $this->methodErrors($node));
    }

    /**
     * Collect errors for properties with an enabled attribute equivalent.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function propertyErrors(Class_ $node): array
    {
        $errors = [];

        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $item) {
                $error = $this->propertyError($item->name->toString(), $item->default, $item->getStartLine());

                if ($error === null) {
                    continue;
                }

                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * Collect errors for method overrides with an enabled attribute equivalent.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function methodErrors(Class_ $node): array
    {
        $errors = [];

        foreach ($node->getMethods() as $method) {
            $name      = $method->name->toString();
            $attribute = self::METHOD_ATTRIBUTES[$name] ?? null;

            if ($attribute === null || in_array($attribute, $this->attributes, true) === false) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Use the #[%s] attribute instead of overriding the %s() method.',
                $attribute,
                $name,
            ))->identifier('sineMaculaLaravel.modelAttribute')->line($method->getStartLine())->build();
        }

        return $errors;
    }

    /**
     * Build the error for a single property declaration, if one applies.
     *
     * @param  string  $name
     * @param  \PhpParser\Node\Expr|null  $default
     * @param  int  $line
     * @return \PHPStan\Rules\RuleError|null
     */
    private function propertyError(string $name, ?Expr $default, int $line): ?RuleError
    {
        $attribute = self::PROPERTY_ATTRIBUTES[$name] ?? null;

        if ($attribute === null || in_array($attribute, $this->attributes, true) === false) {
            return null;
        }

        if ($name === 'hidden' && $default instanceof Array_ && count($default->items) > self::HIDDEN_LIMIT) {
            return null;
        }

        return RuleErrorBuilder::message(sprintf(
            'Use the #[%s] attribute instead of the $%s property.',
            $attribute,
            $name,
        ))->identifier('sineMaculaLaravel.modelAttribute')->line($line)->build();
    }
}
