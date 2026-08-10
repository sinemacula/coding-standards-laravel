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
 * detected from composer.json. Below that the property form stands, because a
 * project supporting older versions cannot emit the attribute at all; but where
 * composer.lock shows it already resolves to 13.2 or above, the legacy form is
 * reported as a lagging floor rather than passed over in silence, so the
 * migration surfaces one model at a time instead of arriving all at once when
 * the floor is eventually raised. $hidden stays a property once it lists more
 * than five fields.
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

    /** @var string The gated-attribute floor as written in a constraint. */
    private const string ATTRIBUTE_FLOOR_LABEL = '13.2';

    /** @var string Identifier for a legacy form the project can replace today. */
    private const string ENFORCE_IDENTIFIER = 'sineMaculaLaravel.modelAttribute';

    /** @var string Identifier for a legacy form only the declared floor still justifies. */
    private const string LAGGING_FLOOR_IDENTIFIER = 'sineMaculaLaravel.modelAttributeLaggingFloor';

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

            if ($attribute === null) {
                continue;
            }

            $error = $this->attributeError(
                $attribute,
                sprintf('overriding the %s() method', $name),
                $method->getStartLine(),
                $scope,
            );

            if ($error === null) {
                continue;
            }

            $errors[] = $error;
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

        if ($attribute === null) {
            return null;
        }

        if ($name === 'hidden' && $default instanceof Array_ && count($default->items) > self::HIDDEN_LIMIT) {
            return null;
        }

        return $this->attributeError($attribute, sprintf('the $%s property', $name), $line, $scope);
    }

    /**
     * Build the error for an enabled attribute's legacy form: the plain
     * replacement where the floor already supports it, otherwise the lagging
     * floor notice where the project resolves to a version that does.
     *
     * @param  string  $attribute
     * @param  string  $legacy
     * @param  int  $line
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return \PHPStan\Rules\RuleError|null
     */
    private function attributeError(string $attribute, string $legacy, int $line, Scope $scope): ?RuleError
    {
        if (in_array($attribute, $this->attributes, true) === false) {
            return null;
        }

        if ($this->isGated($attribute) === false || $this->supportsGatedAttributes($scope)) {
            return $this->error(
                sprintf('Use the #[%s] attribute instead of %s.', $attribute, $legacy),
                self::ENFORCE_IDENTIFIER,
                $line,
            );
        }

        return $this->laggingFloorError($attribute, $legacy, $line, $scope);
    }

    /**
     * Build the notice for a legacy form the project's resolved Laravel could
     * already replace, were its declared floor not holding it back.
     *
     * @param  string  $attribute
     * @param  string  $legacy
     * @param  int  $line
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return \PHPStan\Rules\RuleError|null
     */
    private function laggingFloorError(string $attribute, string $legacy, int $line, Scope $scope): ?RuleError
    {
        $installed = $this->laggingFloorVersion($scope);

        if ($installed === null) {
            return null;
        }

        return $this->error(
            sprintf(
                '#[%s] is available in the Laravel %s this project resolves to, but its declared floor still '
                . 'allows older versions; raise the floor to %s to replace %s.',
                $attribute,
                $installed,
                self::ATTRIBUTE_FLOOR_LABEL,
                $legacy,
            ),
            self::LAGGING_FLOOR_IDENTIFIER,
            $line,
        );
    }

    /**
     * The resolved Laravel version when it supports the gated attributes but
     * the project's declared floor does not, or null when the two agree.
     *
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return string|null
     */
    private function laggingFloorVersion(Scope $scope): ?string
    {
        $floor = $this->declaredFloor($scope);

        if ($floor === null || $this->normaliseVersion($floor) === null) {
            return null;
        }

        $installed = $this->detectInstalledLaravelVersion($scope->getFile());

        if ($installed === null || $this->isLaravelVersionAtLeast($installed, self::ATTRIBUTE_FLOOR) === false) {
            return null;
        }

        return ltrim($installed, 'vV');
    }

    /**
     * Whether the attribute is one of those the 13.2 gate applies to.
     *
     * @param  string  $attribute
     * @return bool
     */
    private function isGated(string $attribute): bool
    {
        return in_array($attribute, self::VERSION_GATED_ATTRIBUTES, true);
    }

    /**
     * Whether the project's Laravel floor reaches the gated-attribute version.
     *
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function supportsGatedAttributes(Scope $scope): bool
    {
        $floor = $this->declaredFloor($scope);

        return $floor !== null && $this->isLaravelVersionAtLeast($floor, self::ATTRIBUTE_FLOOR);
    }

    /**
     * The project's declared Laravel floor, explicit or detected.
     *
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return string|null
     */
    private function declaredFloor(Scope $scope): ?string
    {
        return $this->minLaravelVersion !== ''
            ? $this->minLaravelVersion
            : $this->detectLaravelVersion($scope->getFile());
    }

    /**
     * Build a rule error at a line under the given identifier.
     *
     * @param  string  $message
     * @param  string  $identifier
     * @param  int  $line
     * @return \PHPStan\Rules\RuleError
     */
    private function error(string $message, string $identifier, int $line): RuleError
    {
        return RuleErrorBuilder::message($message)->identifier($identifier)->line($line)->build();
    }
}
