<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\TypeHints;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsAttributes;

/**
 * Require a native parameter type hint, Laravel-aware.
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
        if ($this->isParentConstrained($phpcsFile, $stackPtr)) {
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

    /**
     * Whether a parent fixes the signature, so its parameters cannot be typed.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isParentConstrained(File $phpcsFile, int $stackPtr): bool
    {
        return $this->hasAttribute($phpcsFile, $stackPtr, 'Override')
            || $this->isOverridableTraitMethod($phpcsFile, $stackPtr);
    }

    /**
     * Whether the function is a non-private method declared in a trait.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isOverridableTraitMethod(File $phpcsFile, int $stackPtr): bool
    {
        $conditions = $phpcsFile->getTokens()[$stackPtr]['conditions'];

        if ($conditions === [] || end($conditions) !== T_TRAIT) {
            return false;
        }

        return $phpcsFile->getMethodProperties($stackPtr)['scope'] !== 'private';
    }
}
