<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Structure;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Require role classes to live in their canonical directory.
 *
 * A class is recognised by its name suffix (e.g. `*Controller`, `*Repository`,
 * `*Exception`) and must live somewhere under the matching directory segment -
 * a `*Controller` under `Http/Controllers`. Classes with no recognised role
 * are unaffected.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class RequireRoleDirectorySniff implements Sniff
{
    /** @var array<string, string> Map of class-name suffix to its required namespace path. */
    public array $directories = [
        'Controller'      => 'Http\Controllers',
        'Request'         => 'Http\Requests',
        'Resource'        => 'Http\Resources',
        'Mail'            => 'Mail',
        'Notification'    => 'Notifications',
        'Observer'        => 'Observers',
        'Listener'        => 'Listeners',
        'Policy'          => 'Policies',
        'ServiceProvider' => 'Providers',
        'Repository'      => 'Repositories',
        'Command'         => 'Console\Commands',
        'Exception'       => 'Exceptions',
    ];

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
        $required = $this->requiredDirectory($phpcsFile, $stackPtr);

        if ($required === null || $this->isInNamespacePath($phpcsFile, $required)) {
            return;
        }

        $phpcsFile->addError(
            'This class must live under a "%s" directory.',
            $stackPtr,
            'Misplaced',
            [str_replace('\\', '/', $required)],
        );
    }

    /**
     * Resolve the directory a class must live under, or null for no role.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return string|null
     */
    private function requiredDirectory(File $phpcsFile, int $stackPtr): ?string
    {
        $name = $phpcsFile->getDeclarationName($stackPtr) ?? '';

        foreach ($this->directories as $suffix => $path) {
            if (str_ends_with($name, $suffix)) {
                return $path;
            }
        }

        return null;
    }

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
