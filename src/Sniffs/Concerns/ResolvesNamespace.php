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

        $segments = [];

        for ($i = $namespace + 1; isset($tokens[$i]) && $tokens[$i]['code'] !== T_SEMICOLON; $i++) {
            if ($tokens[$i]['code'] !== T_STRING) {
                continue;
            }

            $segments[] = $tokens[$i]['content'];
        }

        return str_contains('\\' . implode('\\', $segments) . '\\', '\\' . $path . '\\');
    }
}
