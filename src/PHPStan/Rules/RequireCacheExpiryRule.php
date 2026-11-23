<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Require every cache write to expire.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Expr\CallLike>
 */
final readonly class RequireCacheExpiryRule implements Rule
{
    /** @var array<int, string> Receivers whose writes reach a cache store. */
    private const array CACHE_TYPES = [
        'Illuminate\Contracts\Cache\Repository',
        'Illuminate\Contracts\Cache\Store',
        'Illuminate\Contracts\Cache\LockProvider',
        'Illuminate\Cache\CacheManager',
    ];

    /** @var string The cache facade. */
    private const string FACADE = 'Illuminate\Support\Facades\Cache';

    /** @var array<int, string> The cache() helper's parameters; given an array, it puts with the second as TTL. */
    private const array HELPER_PARAMETERS = ['key', 'default'];

    /** @var array<string, array<int, string>> Parameter names of default methods the cache contracts do not declare. */
    private const array SIGNATURES = [
        'putmany'            => ['values', 'ttl'],
        'rememberwithwarmth' => ['key', 'ttl', 'callback'],
        'lock'               => ['name', 'seconds', 'owner'],
    ];

    /** @var array<int, string> Methods that take their TTL second when given an array of values. */
    private const array ARRAY_KEY_METHODS = ['put', 'set'];

    /** @var string The method whose TTL never expires at zero or below. */
    private const string LOCK = 'lock';

    /** @var string Identifier for the reports. */
    private const string IDENTIFIER = 'sineMaculaLaravel.cacheExpiry';

    /**
     * @param  \PHPStan\Reflection\ReflectionProvider  $reflectionProvider
     * @param  array<int, string>  $forbidden
     * @param  array<string, int>  $requireTtl
     */
    public function __construct(

        /** @var \PHPStan\Reflection\ReflectionProvider Resolves the function a call names. */
        private ReflectionProvider $reflectionProvider,

        /** @var array<int, string> Methods that always write without an expiry. */
        private array $forbidden = ['forever', 'rememberForever', 'sear'],

        /** @var array<string, int> Methods that need a TTL, to the TTL's zero-based position. */
        private array $requireTtl = [
            'put'                => 2,
            'add'                => 2,
            'remember'           => 1,
            'rememberWithWarmth' => 1,
            'putMany'            => 1,
            'set'                => 2,
            'setMultiple'        => 1,
            'lock'               => 1,
        ],
    ) {}

    /**
     * The node type this rule inspects.
     *
     * @return string
     */
    #[\Override]
    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * Flag a cache write that stores its key without an expiry.
     *
     * @param  \PhpParser\Node\Expr\CallLike  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        // A nullsafe call also reaches the rule as a plain method call.
        return match (true) {
            $node instanceof FuncCall                                => $this->helperErrors($node, $scope),
            $node instanceof MethodCall, $node instanceof StaticCall => $this->callErrors($node, $scope),
            default                                                  => [],
        };
    }

    /**
     * Flag a `cache([...])` write without a TTL.
     *
     * @param  \PhpParser\Node\Expr\FuncCall  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function helperErrors(FuncCall $node, Scope $scope): array
    {
        if (
            $this->ttlPositionFor('put') === null
            || !$node->name instanceof Name
            || strtolower((string) $this->reflectionProvider->resolveFunctionName($node->name, $scope)) !== 'cache'
        ) {
            return [];
        }

        $values = $this->argumentAt($node->getArgs(), self::HELPER_PARAMETERS, 0, $scope);

        if (!$values instanceof Arg || !$scope->getType($values->value)->isArray()->yes()) {
            return [];
        }

        $ttl = $this->argumentAt($node->getArgs(), self::HELPER_PARAMETERS, 1, $scope);

        return $this->isMissingTtl($ttl, 'put', $scope) ? [$this->missingTtlError('cache')] : [];
    }

    /**
     * Flag a method call on a cache receiver.
     *
     * @param  \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function callErrors(MethodCall|StaticCall $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $receiver = $this->cacheReceiver($node, $scope);

        return $receiver === null ? [] : $this->methodErrors($node, $node->name->toString(), $receiver, $scope);
    }

    /**
     * Flag a forbidden method, or a method that needs a TTL and has none.
     *
     * @param  \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall  $node
     * @param  string  $method
     * @param  \PHPStan\Type\Type  $receiver
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function methodErrors(MethodCall|StaticCall $node, string $method, Type $receiver, Scope $scope): array
    {
        $key = strtolower($method);

        if (in_array($key, array_map(strtolower(...), $this->forbidden), true)) {
            return [
                RuleErrorBuilder::message(sprintf('Cache write "%s()" never expires; store the key with a TTL.', $method))
                    ->identifier(self::IDENTIFIER)
                    ->build(),
            ];
        }

        $position = $this->ttlPositionFor($key);

        if ($position === null) {
            return [];
        }

        $args  = $node->getArgs();
        $names = $this->parameterNames($receiver, $method, $scope);
        $ttl   = $this->argumentAt($args, $names, $this->arrayKeyPosition($key, $args, $names, $scope) ?? $position, $scope);

        return $this->isMissingTtl($ttl, $key, $scope) ? [$this->missingTtlError($method)] : [];
    }

    /**
     * The receiver's type when it is certainly a cache, or null.
     *
     * @param  \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return \PHPStan\Type\Type|null
     */
    private function cacheReceiver(MethodCall|StaticCall $node, Scope $scope): ?Type
    {
        if ($node instanceof StaticCall) {
            return $node->class instanceof Name && $scope->resolveName($node->class) === self::FACADE
                ? new ObjectType(self::FACADE)
                : null;
        }

        $type  = $scope->getType($node->var);
        $cache = TypeCombinator::union(...array_map(
            static fn (string $class): Type => new ObjectType($class),
            self::CACHE_TYPES,
        ));

        return $cache->isSuperTypeOf($type)->yes() ? $type : null;
    }

    /**
     * The TTL position configured for a lowercased method, if it needs one.
     *
     * @param  string  $method
     * @return int|null
     */
    private function ttlPositionFor(string $method): ?int
    {
        return array_change_key_case($this->requireTtl)[$method] ?? null;
    }

    /**
     * The method's parameter names, from the receiver or the known signatures.
     *
     * @param  \PHPStan\Type\Type  $receiver
     * @param  string  $method
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, string>
     */
    private function parameterNames(Type $receiver, string $method, Scope $scope): array
    {
        $reflection = $scope->getMethodReflection($receiver, $method);

        if ($reflection === null) {
            return self::SIGNATURES[strtolower($method)] ?? [];
        }

        return array_map(
            static fn (ParameterReflection $parameter): string => $parameter->getName(),
            $reflection->getVariants()[0]->getParameters(),
        );
    }

    /**
     * Position 1 when a method that takes an array of values is given one.
     *
     * @param  string  $method
     * @param  array<int, \PhpParser\Node\Arg>  $args
     * @param  array<int, string>  $names
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return int|null
     */
    private function arrayKeyPosition(string $method, array $args, array $names, Scope $scope): ?int
    {
        $key = in_array($method, self::ARRAY_KEY_METHODS, true) ? $this->argumentAt($args, $names, 0, $scope) : null;

        return $key instanceof Arg && $scope->getType($key->value)->isArray()->yes() ? 1 : null;
    }

    /**
     * The argument for a parameter position, null when certainly absent, or
     * false when an unpacked or unattributable named argument may supply it.
     *
     * @param  array<int, \PhpParser\Node\Arg>  $args
     * @param  array<int, string>  $names
     * @param  int  $position
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return false|\PhpParser\Node\Arg|null
     */
    private function argumentAt(array $args, array $names, int $position, Scope $scope): Arg|false|null
    {
        $named = $this->namedArgument($args, $names[$position] ?? null);

        foreach ($args as $index => $arg) {
            if ($arg->unpack) {
                return $named instanceof Arg || $scope->getType($arg->value)->isIterableAtLeastOnce()->no() ? $named : false;
            }

            if ($arg->name === null && $index === $position) {
                return $arg;
            }
        }

        return $named;
    }

    /**
     * The argument passed by a parameter's name, null when none is, or false
     * when the name is unknown and a named argument may be it.
     *
     * A method on a union of receivers names a parameter `ttl|seconds`.
     *
     * @param  array<int, \PhpParser\Node\Arg>  $args
     * @param  string|null  $name
     * @return false|\PhpParser\Node\Arg|null
     */
    private function namedArgument(array $args, ?string $name): Arg|false|null
    {
        $named = array_filter($args, static fn (Arg $arg): bool => $arg->name !== null);

        foreach ($named as $arg) {
            if (in_array((string) $arg->name, explode('|', (string) $name), true)) {
                return $arg;
            }
        }

        return $name === null && $named !== [] ? false : null;
    }

    /**
     * Whether the TTL is absent, certainly null, or for a lock never expires.
     *
     * @param  false|\PhpParser\Node\Arg|null  $ttl
     * @param  string  $method
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function isMissingTtl(Arg|false|null $ttl, string $method, Scope $scope): bool
    {
        if (!$ttl instanceof Arg) {
            return $ttl === null;
        }

        $type = $scope->getType($ttl->value);

        return $type->isNull()->yes()
            || ($method === self::LOCK && IntegerRangeType::fromInterval(null, 0)->isSuperTypeOf($type)->yes());
    }

    /**
     * Build the error for a write that needs a TTL and has none.
     *
     * @param  string  $method
     * @return \PHPStan\Rules\RuleError
     */
    private function missingTtlError(string $method): RuleError
    {
        return RuleErrorBuilder::message(sprintf('Cache write "%s()" has no expiry; pass a TTL.', $method))
            ->identifier(self::IDENTIFIER)
            ->build();
    }
}
