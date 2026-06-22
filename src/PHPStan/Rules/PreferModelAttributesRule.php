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
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsLaravelVersion;

/**
 * Prefer model attributes over their legacy property and method forms.
 *
 * Eloquent exposes attribute classes that replace configuration properties
 * (#[Table], #[Fillable], #[Hidden]) and method overrides (#[UseFactory],
 * #[CollectedBy], #[UseEloquentBuilder]). On a model the legacy form is flagged
 * in favour of its attribute - but only for the attributes a project enables,
 * configurable via the `sineMaculaLaravel.modelAttributes` parameter.
 *
 * #[Table]/#[Fillable]/#[Hidden] landed in 13.2, so they are enforced only when
 * the project's Laravel floor reaches 13.2 - taken from `minLaravelVersion` or
 * detected from composer.json; below that, or when unknown, the property form
 * is left alone. $hidden stays a property once it lists more than five fields.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class PreferModelAttributesRule implements Rule
{
    use DetectsLaravelVersion;

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

    /** @var array<int, string> Attributes only available from Laravel 13.2. */
    private const array VERSION_GATED_ATTRIBUTES = ['Table', 'Fillable', 'Hidden'];

    /** @var string The Laravel floor the gated attributes require. */
    private const string ATTRIBUTE_FLOOR = '13.2.0';

    /** @var int Maximum fields before the property form is preferred. */
    private const int HIDDEN_LIMIT = 5;

    /** @var array<int, string> Mandated attribute names. */
    private readonly array $attributes;

    /** @var string Explicit Laravel floor overriding composer.json detection. */
    private readonly string $minLaravelVersion;

    /**
     * @param  array<int, string>  $attributes
     * @param  string  $minLaravelVersion
     */
    public function __construct(array $attributes = ['Table', 'Fillable', 'Hidden'], string $minLaravelVersion = '')
    {
        $this->attributes        = $attributes;
        $this->minLaravelVersion = $minLaravelVersion;
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

        return array_merge($this->propertyErrors($node, $scope), $this->methodErrors($node, $scope));
    }

    /**
     * Collect errors for properties with an enabled attribute equivalent.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function propertyErrors(Class_ $node, Scope $scope): array
    {
        $errors = [];

        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $item) {
                $error = $this->propertyError($item->name->toString(), $item->default, $item->getStartLine(), $scope);

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
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function methodErrors(Class_ $node, Scope $scope): array
    {
        $errors = [];

        foreach ($node->getMethods() as $method) {
            $name      = $method->name->toString();
            $attribute = self::METHOD_ATTRIBUTES[$name] ?? null;

            if ($attribute === null || $this->isEnabled($attribute, $scope) === false) {
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
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return \PHPStan\Rules\RuleError|null
     */
    private function propertyError(string $name, ?Expr $default, int $line, Scope $scope): ?RuleError
    {
        $attribute = self::PROPERTY_ATTRIBUTES[$name] ?? null;

        if ($attribute === null || $this->isEnabled($attribute, $scope) === false) {
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

    /**
     * Whether an attribute is enabled here, honouring the 13.2 version gate.
     *
     * @param  string  $attribute
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function isEnabled(string $attribute, Scope $scope): bool
    {
        if (in_array($attribute, $this->attributes, true) === false) {
            return false;
        }

        if (in_array($attribute, self::VERSION_GATED_ATTRIBUTES, true) === false) {
            return true;
        }

        return $this->supportsGatedAttributes($scope);
    }

    /**
     * Whether the project's Laravel floor reaches the gated-attribute version.
     *
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function supportsGatedAttributes(Scope $scope): bool
    {
        $version = $this->minLaravelVersion !== ''
            ? $this->minLaravelVersion
            : $this->detectLaravelVersion($scope->getFile());

        return $version !== null && $this->isLaravelVersionAtLeast($version, self::ATTRIBUTE_FLOOR);
    }
}
