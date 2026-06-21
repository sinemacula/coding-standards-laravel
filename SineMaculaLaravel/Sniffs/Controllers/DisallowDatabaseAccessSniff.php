<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Controllers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\IdentifiesControllers;

/**
 * Disallow direct database access in controllers.
 *
 * Controllers read through repositories and mutate through services, so they
 * must not query the database directly. Inside a `*Controller`, this flags
 * `DB::` facade calls and static calls on an Eloquent model (a class imported
 * from a `Models` namespace, so facades sharing method names are unaffected).
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowDatabaseAccessSniff implements Sniff
{
    use IdentifiesControllers;

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * Process a potential static method-call name on a controller.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($this->isInController($phpcsFile, $stackPtr) === false) {
            return;
        }

        $class = $this->staticCallClass($phpcsFile, $stackPtr);

        if ($class === null) {
            return;
        }

        if ($class === 'DB') {
            $phpcsFile->addError(
                'Controllers must not query the database directly via the DB facade; use a repository.',
                $stackPtr,
                'Facade',
            );
        } elseif (in_array($class, $this->modelImports($phpcsFile), true)) {
            $phpcsFile->addError(
                'Controllers must not query Eloquent models directly ("%s::"); use a repository.',
                $stackPtr,
                'Eloquent',
                [$class],
            );
        }
    }

    /**
     * Resolve the class name of a static method call anchored on its method.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return string|null
     */
    private function staticCallClass(File $phpcsFile, int $stackPtr): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $colon  = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
        $next   = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if (
            $colon === false
            || $tokens[$colon]['code'] !== T_DOUBLE_COLON
            || $next === false
            || $tokens[$next]['code'] !== T_OPEN_PARENTHESIS
        ) {
            return null;
        }

        $classPtr = $phpcsFile->findPrevious(T_WHITESPACE, $colon - 1, null, true);

        if ($classPtr === false || $tokens[$classPtr]['code'] !== T_STRING) {
            return null;
        }

        return $tokens[$classPtr]['content'];
    }

    /**
     * Collect the short names of classes imported from a `Models` namespace.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return array<int, string>
     */
    private function modelImports(File $phpcsFile): array
    {
        $models = [];
        $use    = 0;

        while (($use = $phpcsFile->findNext(T_USE, $use + 1)) !== false) {
            $name = $this->modelImportName($phpcsFile, $use);

            if ($name === null) {
                continue;
            }

            $models[] = $name;
        }

        return $models;
    }

    /**
     * Resolve the short name of a `use` import from a Models namespace.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $use
     * @return string|null
     */
    private function modelImportName(File $phpcsFile, int $use): ?string
    {
        $tokens   = $phpcsFile->getTokens();
        $segments = [];

        // PHP_CodeSniffer 4.x keeps an import as one T_NAME_QUALIFIED token
        // rather than splitting it into T_STRING segments, so the name is
        // exploded back into segments to find the Models marker and short name.
        $parts = [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED];

        for ($i = $use + 1; isset($tokens[$i]) && $tokens[$i]['code'] !== T_SEMICOLON; $i++) {
            if (!in_array($tokens[$i]['code'], $parts, true)) {
                continue;
            }

            $segments = array_merge($segments, explode('\\', trim($tokens[$i]['content'], '\\')));
        }

        if ($segments === [] || !in_array('Models', $segments, true)) {
            return null;
        }

        return $segments[array_key_last($segments)];
    }
}
