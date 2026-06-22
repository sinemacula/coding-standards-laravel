<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Structure;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesRole;

/**
 * Require a role class to live under its canonical directory.
 *
 * The inverse of the naming rule: a class whose role is resolved (chiefly by
 * identity - what it extends, implements, uses or is attributed with) must live
 * somewhere under that role's configured location, e.g. a controller under
 * `Http/Controllers`. Roles listed in `moduleRootRoles` may instead sit at the
 * package/module root (an entry-point `*ServiceProvider` at `src/`). Classes
 * with no role, or a role that configures no location, are left alone.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class RequireRoleDirectorySniff implements Sniff
{
    use ResolvesRole;

    /** @var array<int, string> Roles whose classes may also sit at the package/module root. */
    public array $moduleRootRoles = ['ServiceProvider'];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_CLASS];
    }

    /**
     * Process a class declaration.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $role  = $this->resolveRole($phpcsFile, $stackPtr);
        $paths = $role === null ? [] : $this->split($this->roleLocations[$role] ?? '');

        if ($paths === [] || $this->isInAnyLocation($phpcsFile, $paths) || $this->isAllowedAtRoot($phpcsFile, $role)) {
            return;
        }

        $phpcsFile->addError(
            'A %s class must live under a "%s" directory.',
            $stackPtr,
            'Misplaced',
            [$role, implode('" or "', array_map(static fn (string $p): string => str_replace('\\', '/', $p), $paths))],
        );
    }

    /**
     * Whether the file's namespace sits under one of the given location paths.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  array<int, string>  $paths
     * @return bool
     */
    private function isInAnyLocation(File $phpcsFile, array $paths): bool
    {
        foreach ($paths as $path) {
            if ($this->isInNamespacePath($phpcsFile, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the role may sit at the module root and the class is there.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  string|null  $role
     * @return bool
     */
    private function isAllowedAtRoot(File $phpcsFile, ?string $role): bool
    {
        return $role !== null && in_array($role, $this->moduleRootRoles, true) && $this->isAtModuleRoot($phpcsFile);
    }

    /**
     * Whether the class sits outside every configured role location (the root).
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return bool
     */
    private function isAtModuleRoot(File $phpcsFile): bool
    {
        foreach ($this->roleLocations as $paths) {
            foreach ($this->split($paths) as $path) {
                if ($this->isInNamespacePath($phpcsFile, $path)) {
                    return false;
                }
            }
        }

        return true;
    }
}
