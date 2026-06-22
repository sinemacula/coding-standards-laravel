<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Concerns;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;

/**
 * Detect the minimum Laravel version a project requires.
 *
 * Walks up from an analysed file to the nearest composer.json and reads the
 * `illuminate/database` constraint (falling back to `laravel/framework`),
 * returning the lower bound of that constraint. A version-gated rule can use
 * this to enforce attributes only when the project's floor supports them.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait DetectsLaravelVersion
{
    /** @var array<string, string|null> Cache of start directory => detected version. */
    private array $laravelVersions = [];

    /**
     * Minimum Laravel version the project containing $file requires, or null.
     *
     * @param  string  $file
     * @return string|null
     */
    protected function detectLaravelVersion(string $file): ?string
    {
        $directory = dirname($file);

        if (!array_key_exists($directory, $this->laravelVersions)) {
            $this->laravelVersions[$directory] = $this->resolveLaravelVersion($directory);
        }

        return $this->laravelVersions[$directory];
    }

    /**
     * Whether a version satisfies a minimum floor, treating pre-releases as the
     * release (so a `^13.2` constraint's `13.2.0.0-dev` floor meets 13.2.0).
     *
     * @param  string  $version
     * @param  string  $floor
     * @return bool
     */
    protected function isLaravelVersionAtLeast(string $version, string $floor): bool
    {
        try {
            $normalised = (new VersionParser)->normalize($version);
        } catch (\Throwable) {
            return false;
        }

        return Comparator::greaterThanOrEqualTo((string) preg_replace('/-.*$/', '', $normalised), $floor);
    }

    /**
     * Resolve the version for the project rooted at or above the directory.
     *
     * @param  string  $directory
     * @return string|null
     */
    private function resolveLaravelVersion(string $directory): ?string
    {
        $composer = $this->locateComposerJson($directory);

        if ($composer === null) {
            return null; // @codeCoverageIgnore
        }

        $constraint = $this->laravelConstraint($composer);

        return $constraint === null ? null : $this->lowerBound($constraint);
    }

    /**
     * Walk up from the directory to the nearest composer.json.
     *
     * @param  string  $directory
     * @return string|null
     */
    private function locateComposerJson(string $directory): ?string
    {
        while (!is_file($directory . '/composer.json')) {
            $parent = dirname($directory);

            if ($parent === $directory) {
                return null; // @codeCoverageIgnore
            }

            $directory = $parent;
        }

        return $directory . '/composer.json';
    }

    /**
     * Read the illuminate/database (or laravel/framework) constraint, if any.
     *
     * @param  string  $composer
     * @return string|null
     */
    private function laravelConstraint(string $composer): ?string
    {
        $data    = json_decode((string) file_get_contents($composer), true);
        $require = is_array($data) && isset($data['require']) && is_array($data['require']) ? $data['require'] : [];

        $constraint = $require['illuminate/database'] ?? $require['laravel/framework'] ?? null;

        return is_string($constraint) ? $constraint : null;
    }

    /**
     * The lower bound of a version constraint, or null if it cannot be parsed.
     *
     * @param  string  $constraint
     * @return string|null
     */
    private function lowerBound(string $constraint): ?string
    {
        try {
            return (new VersionParser)->parseConstraints($constraint)->getLowerBound()->getVersion();
        } catch (\Throwable) {
            return null;
        }
    }
}
