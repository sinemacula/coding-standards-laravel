<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Concerns;

use Composer\Semver\Comparator;
use Composer\Semver\VersionParser;

/**
 * Detect the Laravel versions a project declares and resolves.
 *
 * Walks up from an analysed file to the nearest composer.json and reads the
 * `illuminate/database` constraint (falling back to `laravel/framework`, and a
 * rule may name other packages), returning the lower bound of that constraint.
 * A version-gated rule can use this to enforce attributes only when the
 * project's floor supports them.
 *
 * The resolved version is read separately from composer.lock beside that
 * composer.json, so a rule can tell a project that genuinely cannot use a
 * feature from one whose floor is merely lagging what it runs.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait DetectsLaravelVersion
{
    /** @var array<string, string|null> Cache of start directory => detected version. */
    private array $laravelVersions = [];

    /** @var array<string, string|null> Cache of start directory => resolved version. */
    private array $installedLaravelVersions = [];

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
     * Laravel version the project containing $file resolves to, or null.
     *
     * @param  string  $file
     * @return string|null
     */
    protected function detectInstalledLaravelVersion(string $file): ?string
    {
        $directory = dirname($file);

        if (!array_key_exists($directory, $this->installedLaravelVersions)) {
            $this->installedLaravelVersions[$directory] = $this->resolveInstalledLaravelVersion($directory);
        }

        return $this->installedLaravelVersions[$directory];
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
        $normalised = $this->normaliseVersion($version);

        return $normalised !== null && Comparator::greaterThanOrEqualTo($normalised, $floor);
    }

    /**
     * Normalise a version for comparison, dropping any pre-release suffix, or
     * null when it cannot be parsed.
     *
     * @param  string  $version
     * @return string|null
     */
    protected function normaliseVersion(string $version): ?string
    {
        try {
            return (string) preg_replace('/-.*$/', '', (new VersionParser)->normalize($version));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The packages whose constraint and locked version stand for the project's
     * Laravel, most telling first.
     *
     * @return array<int, string>
     */
    protected function laravelPackages(): array
    {
        return ['illuminate/database', 'laravel/framework'];
    }

    /**
     * The version the project's Laravel is taken to be, from those of the
     * Laravel packages present in preference order: the first of them.
     *
     * @param  array<int, string|null>  $versions
     * @return string|null
     */
    protected function chooseLaravelVersion(array $versions): ?string
    {
        return $versions[0] ?? null;
    }

    /**
     * The Laravel version the project containing $file resolves to, when that
     * reaches $required but the declared $floor does not, or null when the two
     * agree or either cannot be read.
     *
     * @param  string|null  $floor
     * @param  string  $required
     * @param  string  $file
     * @return string|null
     */
    private function laggingLaravelVersion(?string $floor, string $required, string $file): ?string
    {
        if ($floor === null || $this->normaliseVersion($floor) === null) {
            return null;
        }

        $installed = $this->detectInstalledLaravelVersion($file);

        if ($installed === null || $this->isLaravelVersionAtLeast($installed, $required) === false) {
            return null;
        }

        return ltrim($installed, 'vV');
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

        $bounds = array_map(
            fn (mixed $constraint): ?string => is_string($constraint) ? $this->lowerBound($constraint) : null,
            $this->laravelEntries($this->requirements($composer)),
        );

        return $this->chooseLaravelVersion($bounds);
    }

    /**
     * Resolve the locked version for the project rooted at or above the
     * directory.
     *
     * @param  string  $directory
     * @return string|null
     */
    private function resolveInstalledLaravelVersion(string $directory): ?string
    {
        $composer = $this->locateComposerJson($directory);

        if ($composer === null) {
            return null; // @codeCoverageIgnore
        }

        $lock = dirname($composer) . '/composer.lock';

        return is_file($lock) ? $this->lockedLaravelVersion($lock) : null;
    }

    /**
     * Read the resolved version of the first Laravel package a composer.lock
     * carries. An application locks laravel/framework, which replaces the
     * illuminate packages, so the fallback is the usual hit.
     *
     * @param  string  $lock
     * @return string|null
     */
    private function lockedLaravelVersion(string $lock): ?string
    {
        $data     = json_decode((string) file_get_contents($lock), true);
        $packages = is_array($data) ? $data['packages'] ?? null : null;

        if (!is_array($packages)) {
            return null;
        }

        return $this->chooseLaravelVersion($this->laravelEntries($this->lockedVersions($packages)));
    }

    /**
     * Map the readable name => version pairs out of a lock's package list.
     *
     * @param  array<mixed>  $packages
     * @return array<string, string>
     */
    private function lockedVersions(array $packages): array
    {
        $versions = [];

        foreach ($packages as $package) {
            if (!is_array($package)) {
                continue;
            }

            $name    = $package['name']    ?? null;
            $version = $package['version'] ?? null;

            if (!is_string($name) || !is_string($version)) {
                continue;
            }

            $versions[$name] = $version;
        }

        return $versions;
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
     * Read the packages a composer.json requires, by name.
     *
     * @param  string  $composer
     * @return array<array-key, mixed>
     */
    private function requirements(string $composer): array
    {
        $data    = json_decode((string) file_get_contents($composer), true);
        $require = is_array($data) ? $data['require'] ?? null : null;

        return is_array($require) ? $require : [];
    }

    /**
     * The entries a map holds for the Laravel packages, in preference order.
     *
     * @template TEntry
     *
     * @param  array<array-key, TEntry>  $entries
     * @return array<int, TEntry>
     */
    private function laravelEntries(array $entries): array
    {
        $found = [];

        foreach ($this->laravelPackages() as $package) {
            if (!isset($entries[$package])) {
                continue;
            }

            $found[] = $entries[$package];
        }

        return $found;
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
