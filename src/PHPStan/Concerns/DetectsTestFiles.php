<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Concerns;

use PHPStan\Analyser\Scope;

/**
 * Detect files that live under a tests/ directory.
 *
 * Rules whose correctness basis only holds for production code use this to
 * exempt classes declared in tests, where a namespace merely mirrors the
 * production tree. Paths are normalised so Windows separators match too.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait DetectsTestFiles
{
    /**
     * Whether the analysed file lives under a tests/ directory.
     *
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return bool
     */
    private function isTestFile(Scope $scope): bool
    {
        return str_contains(str_replace('\\', '/', $scope->getFile()), '/tests/');
    }
}
