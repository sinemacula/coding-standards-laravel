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
        $tokens    = $phpcsFile->getTokens();
        $namespace = $phpcsFile->findNext(T_NAMESPACE, 0);

        if ($namespace === false) {
            return false;
        }

        $name  = '';
        $parts = [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED];

        // PHP_CodeSniffer 3.x splits a namespace into T_STRING/T_NS_SEPARATOR
        // tokens, but 4.x keeps it as one T_NAME_QUALIFIED token, so both forms
        // are collected to rebuild the name.
        for ($i = $namespace + 1; isset($tokens[$i]) && $tokens[$i]['code'] !== T_SEMICOLON; $i++) {
            if (!in_array($tokens[$i]['code'], $parts, true)) {
                continue;
            }

            $name .= $tokens[$i]['content'];
        }

        return str_contains('\\' . trim($name, '\\') . '\\', '\\' . $path . '\\');
    }
}
