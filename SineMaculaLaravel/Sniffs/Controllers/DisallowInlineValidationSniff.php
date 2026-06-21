<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Controllers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\IdentifiesControllers;

/**
 * Disallow inline validation in controllers.
 *
 * Request-shape validation belongs in a form request, not the controller. This
 * flags `validate()` method calls and `Validator::make()` inside a class named
 * `*Controller`; validation elsewhere (e.g. in a service) is left alone.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowInlineValidationSniff implements Sniff
{
    use IdentifiesControllers;

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * Process a string (potential call name) token.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($this->isValidationCall($phpcsFile, $stackPtr) === false) {
            return;
        }

        if ($this->isInController($phpcsFile, $stackPtr) === false) {
            return;
        }

        $phpcsFile->addError(
            'Inline validation is not allowed in controllers; validate request input in a form request.',
            $stackPtr,
            'Found',
        );
    }

    /**
     * Determine whether the token is a validate() or Validator::make() call.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isValidationCall(File $phpcsFile, int $stackPtr): bool
    {
        $tokens  = $phpcsFile->getTokens();
        $content = $tokens[$stackPtr]['content'];
        $prev    = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($content === 'validate') {
            return $prev !== false
                && in_array($tokens[$prev]['code'], [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], true);
        }

        if ($content === 'make' && $prev !== false && $tokens[$prev]['code'] === T_DOUBLE_COLON) {
            $class = $phpcsFile->findPrevious(T_WHITESPACE, $prev - 1, null, true);

            return $class !== false
                && $tokens[$class]['code']    === T_STRING
                && $tokens[$class]['content'] === 'Validator';
        }

        return false;
    }
}
