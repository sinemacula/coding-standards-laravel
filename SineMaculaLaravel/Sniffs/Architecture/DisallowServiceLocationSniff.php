<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Architecture;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;

/**
 * Disallow service location in class bodies.
 *
 * Collaborators must be constructor-injected, not pulled from the container at
 * call time. This flags the `app()` / `resolve()` container helpers and the
 * `App::make()` facade when used inside a class. Helper names are configurable.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowServiceLocationSniff implements Sniff
{
    use DetectsFunctionCalls;

    /** @var array<int, string> Container helper functions forbidden inside a class body. */
    public array $helpers = ['app', 'resolve'];

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

        if ($this->isInClass($tokens, $stackPtr) === false) {
            return;
        }

        $name = $tokens[$stackPtr]['content'];

        if (in_array($name, $this->helpers, true) && $this->isFunctionCall($phpcsFile, $stackPtr)) {
            $phpcsFile->addError(
                'Service location ("%s()") is not allowed in a class body; inject the dependency instead.',
                $stackPtr,
                'Helper',
                [$name],
            );

            return;
        }

        if (($name !== 'make' && $name !== 'makeWith') || !$this->isFacadeMake($phpcsFile, $stackPtr)) {
            return;
        }

        $phpcsFile->addError(
            'Service location ("App::%s()") is not allowed in a class body; inject the dependency instead.',
            $stackPtr,
            'Facade',
            [$name],
        );
    }

    /**
     * Determine whether the token sits inside a class, trait or enum.
     *
     * @param  array<int, array<string, mixed>>  $tokens
     * @param  int  $stackPtr
     * @return bool
     */
    private function isInClass(array $tokens, int $stackPtr): bool
    {
        foreach ($tokens[$stackPtr]['conditions'] as $code) {
            if (in_array($code, [T_CLASS, T_TRAIT, T_ENUM, T_ANON_CLASS], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the string is the `make`/`makeWith` of an `App::`
     * facade call.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isFacadeMake(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $colon  = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($colon === false || $tokens[$colon]['code'] !== T_DOUBLE_COLON) {
            return false;
        }

        $class = $phpcsFile->findPrevious(T_WHITESPACE, $colon - 1, null, true);

        return $class !== false && $tokens[$class]['code'] === T_STRING && $tokens[$class]['content'] === 'App';
    }
}
