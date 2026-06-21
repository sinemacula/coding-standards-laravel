<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Identify whether a token sits inside a controller class.
 *
 * Controllers are identified by the `*Controller` class-name suffix, which is a
 * universal Laravel convention regardless of base class or route protection.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait IdentifiesControllers
{
    /**
     * Determine whether the token sits inside a class named `*Controller`.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isInController(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        foreach ($tokens[$stackPtr]['conditions'] as $ptr => $code) {
            if ($code !== T_CLASS) {
                continue;
            }

            $name = $phpcsFile->getDeclarationName($ptr);

            if ($name !== null && str_ends_with($name, 'Controller')) {
                return true;
            }
        }

        return false;
    }
}
