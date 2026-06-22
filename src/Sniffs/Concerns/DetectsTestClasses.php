<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;
use SineMacula\CodingStandards\Sniffs\Concerns\DetectsTestClasses as BaseDetectsTestClasses;

/**
 * Detect PHPUnit test files and classes.
 *
 * A file is treated as a test when it lives under a `tests/` directory or
 * declares a class whose name ends in `Test` or that extends a `*TestCase`.
 * Sniffs whose correctness basis only holds for runtime `src` - env()
 * disappearing once config is cached, for instance - use this to exempt tests.
 *
 * The class-level check defers to the base sinemacula/coding-standards
 * DetectsTestClasses; this trait adds only the file-level wrapper.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait DetectsTestClasses
{
    use BaseDetectsTestClasses;

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
}
