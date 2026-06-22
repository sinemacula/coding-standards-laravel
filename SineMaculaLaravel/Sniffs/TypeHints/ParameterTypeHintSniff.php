<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsAttributes;

/**
 * Require a native parameter type hint, Laravel-aware.
 *
 * Every function and method parameter must declare a native type - except on a
 * method carrying `#[\Override]`, whose signature is fixed by the parent it
 * overrides, so typing an inherited untyped parameter would change the
 * signature and fatal. This replaces the Slevomat ParameterTypeHint native-type
 * requirement, which is inheritance-blind, and keeps the same scope (functions
 * and methods, not closures).
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class ParameterTypeHintSniff implements Sniff
{
    use ReadsAttributes;

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * Flag a parameter declared without a native type hint.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($this->hasAttribute($phpcsFile, $stackPtr, 'Override')) {
            return;
        }

        foreach ($phpcsFile->getMethodParameters($stackPtr) as $parameter) {
            if ($parameter['type_hint'] !== '') {
                continue;
            }

            $phpcsFile->addError(
                'Parameter %s must have a native type hint.',
                $parameter['token'],
                'MissingNativeTypeHint',
                [$parameter['name']],
            );
        }
    }
}
