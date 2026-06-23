<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Require a native type hint on class properties, Laravel-aware.
 *
 * Every class property must declare a native type - except the framework-magic
 * properties (`$table`, `$fillable`, `$signature`, …) that override an untyped
 * parent declaration, which PHP forbids typing (it would fatal at class load).
 * The exempt set is the configurable `magicProperties` list, matched by name,
 * since a token-based sniff cannot resolve the parent class. This replaces the
 * Slevomat PropertyTypeHint requirement, which is inheritance-blind.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class PropertyTypeHintSniff implements Sniff
{
    /** @var array<int, string> Property names exempt from the native-type requirement. */
    public array $magicProperties = [
        'signature', 'description', 'table', 'primaryKey', 'keyType', 'incrementing',
        'timestamps', 'dateFormat', 'with', 'withCount', 'perPage', 'fillable', 'guarded',
        'hidden', 'visible', 'casts', 'dates', 'appends', 'attributes', 'dispatchesEvents',
        'touches', 'observables', 'connection', 'escapeWhenCastingToString', 'bindings',
        'singletons', 'defer',
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

        $phpcsFile->addError(
            'Property $%s must have a native type hint.',
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
