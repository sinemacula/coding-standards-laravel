<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Structure;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesNamespace;

/**
 * Require classes in a role directory to carry the role suffix.
 *
 * The inverse of the placement rule: a class under a role directory must be
 * named with that role's suffix - a class under `Http/Controllers` must be a
 * `*Controller`, one under `Repositories` a `*Repository`. Classes outside any
 * role directory are unaffected.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class RequireRoleNamingSniff implements Sniff
{
    use ResolvesNamespace;

    /** @var array<string, string> Map of role directory path to its required class-name suffix. */
    public array $suffixes = [
        'Http\Controllers' => 'Controller',
        'Http\Requests'    => 'Request',
        'Http\Resources'   => 'Resource',
        'Mail'             => 'Mail',
        'Notifications'    => 'Notification',
        'Observers'        => 'Observer',
        'Listeners'        => 'Listener',
        'Policies'         => 'Policy',
        'Providers'        => 'ServiceProvider',
        'Repositories'     => 'Repository',
        'Console\Commands' => 'Command',
        'Exceptions'       => 'Exception',
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
        $suffix = $this->requiredSuffix($phpcsFile);
        $name   = $phpcsFile->getDeclarationName($stackPtr) ?? '';

        if ($suffix === null || str_ends_with($name, $suffix)) {
            return;
        }

        $phpcsFile->addError(
            'A class in this directory must be named with a "%s" suffix.',
            $stackPtr,
            'Misnamed',
            [$suffix],
        );
    }

    /**
     * Resolve the suffix required by the file's role directory, if any.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return string|null
     */
    private function requiredSuffix(File $phpcsFile): ?string
    {
        foreach ($this->suffixes as $path => $suffix) {
            if ($this->isInNamespacePath($phpcsFile, $path)) {
                return $suffix;
            }
        }

        return null;
    }
}
