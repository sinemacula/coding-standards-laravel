<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Debug;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;

/**
 * Disallow debug statements in committed code.
 *
 * Flags the common debug helpers (`dd`, `dump`, `ray`, `var_dump`, `print_r`)
 * when used as function calls, so leftover debugging never reaches a commit.
 * Method/static calls of the same name (e.g. a collection's `->dump()`) are not
 * flagged. The forbidden list is configurable.
 *
 * A truthy second argument turns `print_r` into a plain string function that
 * prints nothing - the form log and exception messages are built from - so it
 * is left alone. `var_dump` has no such form and is always flagged.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowDebugStatementsSniff implements Sniff
{
    use DetectsFunctionCalls;

    /** @var array<int, string> Functions a truthy second argument turns into a string function. */
    private const array RETURNS_ON_SECOND_ARGUMENT = ['print_r', 'var_export'];

    /** @var array<int, int|string> Second-argument literals that still print. */
    private const array PRINTING_LITERALS = [T_FALSE, T_NULL];

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

        if ($this->returnsInsteadOfPrinting($phpcsFile, $stackPtr, strtolower($name))) {
            return;
        }

        $phpcsFile->addError(
            'Debug statement "%s()" must not be committed; remove it.',
            $stackPtr,
            'Found',
            [$name],
        );
    }

    /**
     * Whether the call returns its output rather than printing it, which makes
     * it an ordinary string function rather than a debug statement.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  string  $name
     * @return bool
     */
    private function returnsInsteadOfPrinting(File $phpcsFile, int $stackPtr, string $name): bool
    {
        if (in_array($name, self::RETURNS_ON_SECOND_ARGUMENT, true) === false) {
            return false;
        }

        return $this->hasTruthySecondArgument($phpcsFile, $stackPtr);
    }

    /**
     * Whether the call's second argument is present and is anything other than
     * a literal that leaves it printing.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function hasTruthySecondArgument(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $opener = (int) $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);
        $closer = $tokens[$opener]['parenthesis_closer'];
        $comma  = $this->separator($phpcsFile, $opener, $closer);
        $first  = $comma === null ? false : $phpcsFile->findNext(Tokens::$emptyTokens, $comma + 1, $closer, true);

        if ($first === false) {
            return false;
        }

        $last = $phpcsFile->findPrevious(Tokens::$emptyTokens, $closer - 1, $comma, true);

        return $first !== $last || in_array($tokens[$first]['code'], self::PRINTING_LITERALS, true) === false;
    }

    /**
     * Pointer to the comma separating the first two arguments, or null when the
     * call has only one. Commas nested in an inner expression are skipped.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $opener
     * @param  int  $closer
     * @return int|null
     */
    private function separator(File $phpcsFile, int $opener, int $closer): ?int
    {
        $tokens = $phpcsFile->getTokens();

        $i = $opener + 1;

        while ($i < $closer) {
            if ($tokens[$i]['code'] === T_COMMA) {
                return $i;
            }

            $skip = $tokens[$i]['parenthesis_closer'] ?? $tokens[$i]['bracket_closer'] ?? $i;
            $i    = $skip + 1;
        }

        return null;
    }
}
