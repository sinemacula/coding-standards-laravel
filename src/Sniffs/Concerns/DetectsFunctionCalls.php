<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Detect direct global function calls in a token stream.
 *
 * Shared by sniffs that flag a bare function call (e.g. `dd()`, `env()`) while
 * ignoring same-named method calls, static calls and declarations.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait DetectsFunctionCalls
{
    /**
     * Determine whether the string token is a direct function call (not a
     * method, static call or declaration).
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isFunctionCall(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $next   = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_OPEN_PARENTHESIS) {
            return false;
        }

        $prev           = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
        $notCallContext = [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW, T_NS_SEPARATOR];

        return $prev === false || in_array($tokens[$prev]['code'], $notCallContext, true) === false;
    }
}
