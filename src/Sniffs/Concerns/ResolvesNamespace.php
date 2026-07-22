<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Resolve information about a file's namespace.
 *
 * Used by the structural sniffs to map a class to its role directory and back.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait ResolvesNamespace
{
    /**
     * Determine whether the file's namespace contains the given segment path.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  string  $path
     * @return bool
     */
    private function isInNamespacePath(File $phpcsFile, string $path): bool
    {
        return str_contains('\\' . $this->namespaceName($phpcsFile) . '\\', '\\' . $path . '\\');
    }

    /**
     * Resolve the file's namespace name, or an empty string without one.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return string
     */
    private function namespaceName(File $phpcsFile): string
    {
        $tokens    = $phpcsFile->getTokens();
        $namespace = $phpcsFile->findNext(T_NAMESPACE, 0);

        if ($namespace === false) {
            return '';
        }

        $name  = '';
        $parts = [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED];

        // PHP_CodeSniffer 3.x splits a namespace into T_STRING/T_NS_SEPARATOR
        // tokens, but 4.x keeps it as one T_NAME_QUALIFIED token, so both forms
        // are collected to rebuild the name. The declaration ends at `;` or, in
        // the braced syntax, at `{`.
        for ($i = $namespace; isset($tokens[$i]) && !in_array($tokens[$i]['code'], [T_SEMICOLON, T_OPEN_CURLY_BRACKET], true); $i++) {
            if (!in_array($tokens[$i]['code'], $parts, true)) {
                continue;
            }

            $name .= $tokens[$i]['content'];
        }

        return trim($name, '\\');
    }
}
