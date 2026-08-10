<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDocblockTags;

/**
 * Require a native type hint on class properties, Laravel-aware.
 *
 * Every class property must declare a native type - except one that overrides
 * an untyped parent declaration, which PHP forbids typing (it would fatal at
 * class load). A token-based sniff cannot resolve the parent class, so the
 * exemption is name-matched against the configurable `magicProperties` list,
 * which covers the framework base classes an application extends: models,
 * factories, commands, migrations, API resources, middleware, the kernels,
 * service providers and the exception handler. This replaces the Slevomat
 * PropertyTypeHint requirement, which is inheritance-blind.
 *
 * The list cannot know about third-party or application base classes, so
 * `@untypeable` on the property is the escape hatch for any other property
 * whose untyped parent declaration puts a native type out of reach.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class PropertyTypeHintSniff implements Sniff
{
    use ReadsDocblockTags;

    /** @var array<int, string> Property names exempt from the native-type requirement. */
    public array $magicProperties = [
        // Console commands
        'signature', 'description', 'help', 'aliases', 'hidden',
        // Eloquent models
        'table', 'primaryKey', 'keyType', 'incrementing', 'timestamps', 'dateFormat',
        'with', 'withCount', 'perPage', 'fillable', 'guarded', 'visible', 'casts',
        'dates', 'appends', 'attributes', 'dispatchesEvents', 'touches', 'observables',
        'connection', 'escapeWhenCastingToString', 'snakeAttributes',
        // Factories, migrations and API resources
        'model', 'withinTransaction', 'wrap', 'collects',
        // Middleware and the HTTP/console kernels
        'except', 'proxies', 'headers', 'addHttpCookie', 'middleware', 'middlewareGroups',
        'middlewareAliases', 'middlewarePriority', 'routeMiddleware', 'commands', 'bootstrappers',
        // Service providers and the exception handler
        'bindings', 'singletons', 'defer', 'policies', 'listen', 'subscribe', 'observers',
        'namespace', 'dontReport', 'dontFlash', 'levels',
    ];

    /** @var array<int, int|string> Scopes whose direct variables are properties. */
    private array $propertyScopes = [T_CLASS, T_TRAIT, T_ANON_CLASS];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_VARIABLE];
    }

    /**
     * Flag a class property declared without a native type hint.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($this->isClassProperty($phpcsFile, $stackPtr) === false) {
            return;
        }

        $name = ltrim($phpcsFile->getTokens()[$stackPtr]['content'], '$');

        if (in_array($name, $this->magicProperties, true) || $phpcsFile->getMemberProperties($stackPtr)['type'] !== '') {
            return;
        }

        if ($this->hasDocblockTag($phpcsFile, $stackPtr, '@untypeable')) {
            return;
        }

        $phpcsFile->addError(
            'Property $%s must have a native type hint, or be marked @untypeable if it '
            . 'overrides an untyped parent declaration that PHP forbids typing.',
            $stackPtr,
            'MissingNativeTypeHint',
            [$name],
        );
    }

    /**
     * Whether the variable is a property declared directly in a class body.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isClassProperty(File $phpcsFile, int $stackPtr): bool
    {
        $token      = $phpcsFile->getTokens()[$stackPtr];
        $conditions = $token['conditions'];

        if ($conditions === [] || empty($token['nested_parenthesis']) === false) {
            return false;
        }

        return in_array(end($conditions), $this->propertyScopes, true);
    }
}
