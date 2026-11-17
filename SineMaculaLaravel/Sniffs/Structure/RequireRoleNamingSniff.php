<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Structure;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDeclarationNames;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesRole;

/**
 * Require a class to follow its role's naming convention.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class RequireRoleNamingSniff implements Sniff
{
    use ReadsDeclarationNames, ResolvesRole;

    /** @var array<string, string> Role => comma-separated suffixes the name must end with one of. */
    public array $requireSuffix = [
        'Controller'      => 'Controller',
        'ServiceProvider' => 'ServiceProvider',
        'FormRequest'     => 'Request',
        'Resource'        => 'Resource',
        'Policy'          => 'Policy',
    ];

    /** @var array<string, string> Role => comma-separated suffixes the name must not end with. */
    public array $forbidSuffix = [
        'Model' => 'Model,Entity',
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
        $role = $this->resolveRole($phpcsFile, $stackPtr);

        if ($role === null) {
            return;
        }

        $name = $this->declarationName($phpcsFile, $stackPtr);

        $this->checkRequired($phpcsFile, $stackPtr, $role, $name);
        $this->checkForbidden($phpcsFile, $stackPtr, $role, $name);
    }

    /**
     * Flag a class whose name lacks its role's required suffix.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  string  $role
     * @param  string  $name
     * @return void
     */
    private function checkRequired(File $phpcsFile, int $stackPtr, string $role, string $name): void
    {
        $suffixes = $this->split($this->requireSuffix[$role] ?? '');

        if ($suffixes === [] || $this->endsWithAny($name, $suffixes)) {
            return;
        }

        $phpcsFile->addError(
            'A %s class must be named with a "%s" suffix.',
            $stackPtr,
            'Misnamed',
            [$role, implode('" or "', $suffixes)],
        );
    }

    /**
     * Flag a class whose name carries a suffix its role forbids.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  string  $role
     * @param  string  $name
     * @return void
     */
    private function checkForbidden(File $phpcsFile, int $stackPtr, string $role, string $name): void
    {
        foreach ($this->split($this->forbidSuffix[$role] ?? '') as $suffix) {
            if (str_ends_with($name, $suffix)) {
                $phpcsFile->addError(
                    'A %s class must not be named with a "%s" suffix; Laravel names it bare.',
                    $stackPtr,
                    'Forbidden',
                    [$role, $suffix],
                );

                return;
            }
        }
    }

    /**
     * Whether the name ends with any of the given suffixes.
     *
     * @param  string  $name
     * @param  array<int, string>  $suffixes
     * @return bool
     */
    private function endsWithAny(string $name, array $suffixes): bool
    {
        foreach ($suffixes as $suffix) {
            if (str_ends_with($name, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
