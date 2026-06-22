<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Detect PHPUnit test files and classes.
 *
 * A file is treated as a test when it lives under a `tests/` directory or
 * declares a class whose name ends in `Test` or that extends a `*TestCase`.
 * Sniffs whose correctness basis only holds for runtime `src` - env()
 * disappearing once config is cached, for instance - use this to exempt tests.
 *
 * Mirrors the base sinemacula/coding-standards DetectsTestClasses (class-level)
 * and adds the file-level check; once that base trait ships in a release the
 * class-level method here can defer to it.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait DetectsTestClasses
{
    /**
     * Whether the processed file is a test file.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return bool
     */
    protected function isTestFile(File $phpcsFile): bool
    {
        if (str_contains(str_replace('\\', '/', $phpcsFile->getFilename()), '/tests/')) {
            return true;
        }

        $classPtr = 0;

        while (($classPtr = $phpcsFile->findNext(T_CLASS, $classPtr + 1)) !== false) {
            if ($this->isTestClass($phpcsFile, $classPtr)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the class declared at the pointer is a test class.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return bool
     */
    protected function isTestClass(File $phpcsFile, int $classPtr): bool
    {
        if (str_ends_with((string) $phpcsFile->getDeclarationName($classPtr), 'Test')) {
            return true;
        }

        $parent = $phpcsFile->findExtendedClassName($classPtr);

        return $parent !== false && str_ends_with($parent, 'TestCase');
    }
}
