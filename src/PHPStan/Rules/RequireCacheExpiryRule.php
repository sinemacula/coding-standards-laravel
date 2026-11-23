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
use PHPStan\Type\Constant\ConstantIntegerType;
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

        $values = $this->argumentType($node->getArgs(), self::HELPER_PARAMETERS, 0, $scope);

        if (!$values instanceof Type || !$values->isArray()->yes()) {
            return [];
        }

        $ttl = $this->argumentType($node->getArgs(), self::HELPER_PARAMETERS, 1, $scope);

        return $this->isMissingTtl($ttl, 'put') ? [$this->missingTtlError('cache')] : [];
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

        $names = $this->parameterNames($receiver, $method, $scope);
        $ttl   = $this->ttlType($key, $node->getArgs(), $names, $position, $scope);

        return $this->isMissingTtl($ttl, $key) ? [$this->missingTtlError($method)] : [];
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
     * The TTL's type, taken second when an array of values is put, or false
     * when the key cannot tell which form the call takes.
     *
     * @param  string  $method
     * @param  array<int, \PhpParser\Node\Arg>  $args
     * @param  array<int, string>  $names
     * @param  int  $position
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return false|\PHPStan\Type\Type|null
     */
    private function ttlType(string $method, array $args, array $names, int $position, Scope $scope): false|Type|null
    {
        if (!in_array($method, self::ARRAY_KEY_METHODS, true)) {
            return $this->argumentType($args, $names, $position, $scope);
        }

        $key     = $this->argumentType($args, $names, 0, $scope);
        $isArray = $key instanceof Type ? $key->isArray() : null;

        return match (true) {
            $isArray?->yes() === true => $this->argumentType($args, $names, 1, $scope),
            $isArray?->no()  === true => $this->argumentType($args, $names, $position, $scope),
            default                   => false,
        };
    }

    /**
     * The type of the argument for a parameter position, null when certainly
     * absent, or false when an unpacked or unattributable named argument may
     * supply it.
     *
     * @param  array<int, \PhpParser\Node\Arg>  $args
     * @param  array<int, string>  $names
     * @param  int  $position
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return false|\PHPStan\Type\Type|null
     */
    private function argumentType(array $args, array $names, int $position, Scope $scope): false|Type|null
    {
        $named      = $this->namedArgument($args, $names[$position] ?? null);
        $positional = $this->positionalType($args, $position, $scope);

        if ($named instanceof Arg) {
            return $scope->getType($named->value);
        }

        return $positional === null ? $named : $positional;
    }

    /**
     * The type passed at a position, reading into unpacked lists of known size,
     * null when nothing is, or false when an unpacked argument may be.
     *
     * @param  array<int, \PhpParser\Node\Arg>  $args
     * @param  int  $position
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return false|\PHPStan\Type\Type|null
     */
    private function positionalType(array $args, int $position, Scope $scope): false|Type|null
    {
        $at = 0;

        foreach (array_filter($args, static fn (Arg $arg): bool => $arg->name === null) as $arg) {
            $type = $scope->getType($arg->value);
            $size = $arg->unpack ? $this->listSize($type) : 1;

            if ($size === null) {
                return false;
            }

            if ($at + $size > $position) {
                return $arg->unpack ? $type->getOffsetValueType(new ConstantIntegerType($position - $at)) : $type;
            }

            $at += $size;
        }

        return null;
    }

    /**
     * The number of values an unpacked list supplies, when certain.
     *
     * @param  \PHPStan\Type\Type  $type
     * @return int|null
     */
    private function listSize(Type $type): ?int
    {
        $size = $type->getArraySize()->getConstantScalarValues();

        return $type->isList()->yes() && count($size) === 1 && is_int($size[0]) ? $size[0] : null;
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
     * @param  false|\PHPStan\Type\Type|null  $ttl
     * @param  string  $method
     * @return bool
     */
    private function isMissingTtl(false|Type|null $ttl, string $method): bool
    {
        if (!$ttl instanceof Type) {
            return $ttl === null;
        }

        return $ttl->isNull()->yes()
            || ($method === self::LOCK && IntegerRangeType::fromInterval(null, 0)->isSuperTypeOf($ttl)->yes());
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
