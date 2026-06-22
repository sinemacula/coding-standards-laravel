<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsAttributes;

/**
 * Require a native return type hint, Laravel-aware.
 *
 * Every function, method and closure must declare a native return type - except
 * on a method carrying `#[\Override]`, whose signature the parent fixes.
 * Constructors, destructors and clone handlers, which cannot declare a return
 * type, are skipped. This replaces the Slevomat ReturnTypeHint requirement,
 * which is inheritance-blind, and keeps the same scope.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class ReturnTypeHintSniff implements Sniff
{
    use ReadsAttributes;

    /** @var array<int, string> Methods that cannot declare a return type. */
    private array $withoutReturnType = ['__construct', '__destruct', '__clone'];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_FUNCTION, T_CLOSURE];
    }

    /**
     * Flag a function declared without a native return type hint.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $name = $phpcsFile->getTokens()[$stackPtr]['code'] === T_FUNCTION
            ? $phpcsFile->getDeclarationName($stackPtr)
            : null;

        if (in_array($name, $this->withoutReturnType, true) || $this->hasAttribute($phpcsFile, $stackPtr, 'Override')) {
            return;
        }

        if ($phpcsFile->getMethodProperties($stackPtr)['return_type'] !== '') {
            return;
        }

        $phpcsFile->addError(
            '%s must declare a native return type hint.',
            $stackPtr,
            'MissingNativeTypeHint',
            [$name !== null ? $name . '()' : 'Closure'],
        );
    }
}
