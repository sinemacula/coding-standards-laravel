<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Debug;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;

/**
 * Disallow debug statements in committed code.
 *
 * Flags the common debug helpers (`dd`, `dump`, `ray`, `var_dump`, `print_r`)
 * when used as function calls, so leftover debugging never reaches a commit.
 * Method/static calls of the same name (e.g. a collection's `->dump()`) are not
 * flagged. The forbidden list is configurable.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowDebugStatementsSniff implements Sniff
{
    use DetectsFunctionCalls;

    /** @var array<int, string> Debug functions forbidden in committed code. */
    public array $functions = ['dd', 'dump', 'ray', 'var_dump', 'print_r'];

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
        $name   = $tokens[$stackPtr]['content'];

        if (in_array(strtolower($name), $this->functions, true) === false) {
            return;
        }

        if ($this->isFunctionCall($phpcsFile, $stackPtr) === false) {
            return;
        }

        $phpcsFile->addError(
            'Debug statement "%s()" must not be committed; remove it.',
            $stackPtr,
            'Found',
            [$name],
        );
    }
}
