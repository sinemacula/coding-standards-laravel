<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Read the name of a class, interface, trait, enum or function declaration.
 *
 * Normalises the two "no name" values the supported PHP_CodeSniffer lines
 * return, so a sniff can compare the name directly.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait ReadsDeclarationNames
{
    /**
     * Resolve a declaration's name, or an empty string when it has none.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return string
     */
    private function declarationName(File $phpcsFile, int $stackPtr): string
    {
        // An unnamed declaration - live coding - is null on 3.x but an empty
        // string on 4.x, so the cast is what makes the two lines agree.
        return (string) $phpcsFile->getDeclarationName($stackPtr);
    }
}
