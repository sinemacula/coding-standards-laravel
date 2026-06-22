<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Configuration;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsTestClasses;

/**
 * Disallow env() outside config files.
 *
 * `env()` returns null once `config:cache` has run, so reading it anywhere
 * other than a `config/` file means the value silently disappears in
 * production. Everywhere else must read through `config()`. Test code (a file
 * under `tests/`, a `*Test` class, or a testbench `*TestCase`) is exempt, since
 * config caching never happens there. Method and static calls named `env()`
 * are not flagged.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowEnvOutsideConfigSniff implements Sniff
{
    use DetectsFunctionCalls;
    use DetectsTestClasses;

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
     * Process a string (potential call name) token.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (strtolower($tokens[$stackPtr]['content']) !== 'env') {
            return;
        }

        if (
            $this->isFunctionCall($phpcsFile, $stackPtr) === false
            || $this->isConfigFile($phpcsFile)
            || $this->isTestFile($phpcsFile)
        ) {
            return;
        }

        $phpcsFile->addError(
            'env() may only be used in config/ files; read configuration via config() instead.',
            $stackPtr,
            'Found',
        );
    }

    /**
     * Determine whether the processed file lives inside a config/ directory.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return bool
     */
    private function isConfigFile(File $phpcsFile): bool
    {
        $path = str_replace('\\', '/', $phpcsFile->getFilename());

        return str_contains($path, '/config/');
    }
}
