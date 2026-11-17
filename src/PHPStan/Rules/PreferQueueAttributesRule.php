<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use Composer\Semver\Comparator;
use PhpParser\Node;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsLaravelVersion;

/**
 * Prefer the queue attributes over the properties they replace.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @SuppressWarnings("php:S1192")
 *
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\InClassNode>
 */
final class PreferQueueAttributesRule implements Rule
{
    use DetectsLaravelVersion;

    /** @var array<string, array<string, string>> Queued kind, most specific first => property => attribute floor. */
    private const array FLOORS = [
        'Illuminate\Mail\Mailable' => [
            'tries'                   => '13.4',
            'timeout'                 => '13.4',
            'backoff'                 => '13.4',
            'maxExceptions'           => '13.4',
            'failOnTimeout'           => '13.36',
            'deleteWhenMissingModels' => '13.36',
        ],
        'Illuminate\Notifications\Notification' => [
            'tries'                   => '13.4',
            'timeout'                 => '13.4',
            'backoff'                 => '13.4',
            'maxExceptions'           => '13.4',
            'deleteWhenMissingModels' => '13.4',
            'queue'                   => '13.4',
            'connection'              => '13.4',
            'delay'                   => '13.4',
            'failOnTimeout'           => '13.25',
        ],
        'Illuminate\Contracts\Broadcasting\ShouldBroadcast' => [
            'tries'                   => '13.4',
            'timeout'                 => '13.4',
            'backoff'                 => '13.4',
            'maxExceptions'           => '13.4',
            'deleteWhenMissingModels' => '13.4',
            'uniqueFor'               => '13.36',
        ],
        'Illuminate\Contracts\Queue\ShouldQueue' => [
            'tries'                   => '13.4',
            'timeout'                 => '13.4',
            'backoff'                 => '13.4',
            'maxExceptions'           => '13.4',
            'failOnTimeout'           => '13.4',
            'deleteWhenMissingModels' => '13.4',
            'queue'                   => '13.4',
            'connection'              => '13.4',
            'uniqueFor'               => '13.4',
            'delay'                   => '13.24',
            'debounceFor'             => '13.26',
        ],
    ];

    /** @var array<string, string> Setting => the shape of value its attribute takes. */
    private const array SHAPES = [
        'tries'                   => 'int',
        'timeout'                 => 'int',
        'maxExceptions'           => 'int',
        'uniqueFor'               => 'int',
        'delay'                   => 'int',
        'debounceFor'             => 'int',
        'backoff'                 => 'backoff',
        'queue'                   => 'name',
        'connection'              => 'name',
        'failOnTimeout'           => 'flag',
        'deleteWhenMissingModels' => 'flag',
    ];

    /** @var array<string, array<int, string>> Setting => the methods the framework calls in its place. */
    private const array METHOD_FORMS = [
        'tries'      => ['tries'],
        'backoff'    => ['backoff'],
        'uniqueFor'  => ['uniqueFor'],
        'queue'      => ['viaQueue', 'viaQueues'],
        'connection' => ['viaConnection', 'viaConnections'],
        'delay'      => ['withDelay'],
    ];

    /** @var array<int, string> Settings the Queueable setters change at runtime, which an attribute cannot follow. */
    private const array RUNTIME_SETTINGS = ['queue', 'connection', 'delay'];

    /** @var string The trait whose setters and chains rewrite the runtime settings. */
    private const string QUEUEABLE_TRAIT = 'Illuminate\Bus\Queueable';

    /** @var string Identifier for a property the project can replace today. */
    private const string ENFORCE_IDENTIFIER = 'sineMaculaLaravel.queueAttribute';

    /** @var string Identifier for a property only the declared floor still justifies. */
    private const string LAGGING_FLOOR_IDENTIFIER = 'sineMaculaLaravel.queueAttributeLaggingFloor';

    /** @var string Explicit Laravel floor overriding composer.json detection. */
    private readonly string $minLaravelVersion;

    /**
     * @param  string  $minLaravelVersion
     */
    public function __construct(string $minLaravelVersion = '')
    {
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
        return InClassNode::class;
    }

    /**
     * Flag the queue settings a queued class declares as properties.
     *
     * @param  \PHPStan\Node\InClassNode  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $class  = $node->getClassReflection();
        $floors = $this->floorsFor($class);
        $errors = [];

        foreach ($node->getOriginalNode()->getProperties() as $property) {
            foreach ($this->settingItems($property) as $item) {
                $error = $this->propertyError($item, $floors, $class, $scope);

                if ($error === null) {
                    continue;
                }

                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * The packages that stand for the project's Laravel, queue-side components
     * first.
     *
     * @return array<int, string>
     */
    protected function laravelPackages(): array
    {
        return [
            'illuminate/queue', 'illuminate/bus', 'illuminate/mail', 'illuminate/notifications',
            'illuminate/broadcasting', 'illuminate/events', 'illuminate/support', 'illuminate/database',
            'laravel/framework',
        ];
    }

    /**
     * The lowest version the components declare or lock, or null when any of
     * them cannot be read.
     *
     * Each component reads only its own kinds' attributes, and the runtime
     * precedence lives in illuminate/support.
     *
     * @param  array<int, string|null>  $versions
     * @return string|null
     */
    protected function chooseLaravelVersion(array $versions): ?string
    {
        $lowest   = null;
        $boundary = '';

        foreach ($versions as $version) {
            $normalised = $version === null ? null : $this->normaliseVersion($version);

            if ($normalised === null) {
                return null;
            }

            if ($lowest !== null && Comparator::greaterThanOrEqualTo($normalised, $boundary)) {
                continue;
            }

            $lowest   = $version;
            $boundary = $normalised;
        }

        return $lowest;
    }

    /**
     * The attribute floors, by setting, for the class's most specific kind.
     *
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @return array<string, string>
     */
    private function floorsFor(ClassReflection $class): array
    {
        foreach (self::FLOORS as $kind => $floors) {
            if ($class->is($kind) === false) {
                continue;
            }

            if ($class->hasTraitUse(self::QUEUEABLE_TRAIT)) {
                return array_diff_key($floors, array_flip(self::RUNTIME_SETTINGS));
            }

            return $floors;
        }

        return [];
    }

    /**
     * The items of a property declaration that hold a setting's value.
     *
     * Only a public instance property is visible to the framework, and a hooked
     * one computes its value.
     *
     * @param  \PhpParser\Node\Stmt\Property  $property
     * @return array<int, \PhpParser\Node\PropertyItem>
     */
    private function settingItems(Property $property): array
    {
        return $property->isPublic() && !$property->isStatic() && $property->hooks === [] ? $property->props : [];
    }

    /**
     * Build the error for a property declaring a queue setting, if one applies.
     *
     * @param  \PhpParser\Node\PropertyItem  $item
     * @param  array<string, string>  $floors
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return \PHPStan\Rules\RuleError|null
     */
    private function propertyError(PropertyItem $item, array $floors, ClassReflection $class, Scope $scope): ?RuleError
    {
        $name  = $item->name->toString();
        $floor = $floors[$name] ?? null;

        if ($floor === null || $item->default === null || $this->isAnsweredByAMethod($name, $class)) {
            return null;
        }

        return $this->fitsTheAttribute(self::SHAPES[$name], $scope->getType($item->default))
            ? $this->error($name, $floor, $item->getStartLine(), $scope)
            : null;
    }

    /**
     * Whether the class answers the setting with a method the framework calls
     * in place of reading the property, such as `tries()` or `viaQueue()`.
     *
     * @param  string  $name
     * @param  \PHPStan\Reflection\ClassReflection  $class
     * @return bool
     */
    private function isAnsweredByAMethod(string $name, ClassReflection $class): bool
    {
        foreach (self::METHOD_FORMS[$name] ?? [] as $method) {
            if ($class->hasNativeMethod($method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a default is a value the setting's attribute can carry.
     *
     * @param  string  $shape
     * @param  \PHPStan\Type\Type  $type
     * @return bool
     */
    private function fitsTheAttribute(string $shape, Type $type): bool
    {
        return match ($shape) {
            'int'     => $type->isInteger()->yes(),
            'backoff' => $type->isInteger()->yes() || $type->getIterableValueType()->isInteger()->yes(),
            'name'    => $type->isString()->yes()  || $type->isEnum()->yes(),
            default   => $type->isTrue()->yes(),
        };
    }

    /**
     * Build the error for a property its attribute replaces, or the lagging
     * floor notice where only the resolved version supports it.
     *
     * @param  string  $name
     * @param  string  $floor
     * @param  int  $line
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return \PHPStan\Rules\RuleError|null
     */
    private function error(string $name, string $floor, int $line, Scope $scope): ?RuleError
    {
        $attribute = ucfirst($name);
        $declared  = $this->minLaravelVersion !== ''
            ? $this->minLaravelVersion
            : $this->detectLaravelVersion($scope->getFile());

        if ($declared !== null && $this->isLaravelVersionAtLeast($declared, $floor)) {
            return RuleErrorBuilder::message(sprintf('Use the #[%s] attribute instead of the $%s property.', $attribute, $name))
                ->identifier(self::ENFORCE_IDENTIFIER)
                ->line($line)
                ->build();
        }

        $installed = $this->laggingLaravelVersion($declared, $floor, $scope->getFile());

        if ($installed === null) {
            return null;
        }

        return RuleErrorBuilder::message(sprintf(
            '#[%s] is available in the Laravel %s this project resolves to, but its declared floor still '
            . 'allows older versions; raise the floor to %s to replace the $%s property.',
            $attribute,
            $installed,
            $floor,
            $name,
        ))
            ->identifier(self::LAGGING_FLOOR_IDENTIFIER)
            ->line($line)
            ->build();
    }
}
