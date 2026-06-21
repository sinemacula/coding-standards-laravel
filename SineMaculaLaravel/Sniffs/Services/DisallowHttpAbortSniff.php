<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Services;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;

/**
 * Disallow HTTP aborts in services.
 *
 * A class in the service layer must stay independent of HTTP: it throws domain
 * exceptions rather than aborting the request. Inside a class whose namespace
 * has a `Services` segment, this flags the `abort()` / `abort_if()` /
 * `abort_unless()` helpers and the instantiation of any `*HttpException`.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowHttpAbortSniff implements Sniff
{
    use DetectsFunctionCalls;

    /** @var array<int, string> The HTTP-abort helper functions. */
    public array $functions = ['abort', 'abort_if', 'abort_unless'];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_STRING, T_NEW];
    }

    /**
     * Process a string or `new` token inside a service.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        if ($this->isInServiceNamespace($phpcsFile) === false) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_NEW) {
            $this->flagHttpException($phpcsFile, $stackPtr);

            return;
        }

        $this->flagAbortCall($phpcsFile, $stackPtr);
    }

    /**
     * Flag a call to one of the HTTP-abort helpers.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    private function flagAbortCall(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $name   = $tokens[$stackPtr]['content'];

        if (!in_array(strtolower($name), $this->functions, true) || !$this->isFunctionCall($phpcsFile, $stackPtr)) {
            return;
        }

        $phpcsFile->addError(
            'Services must not abort the request ("%s()"); throw a domain exception instead.',
            $stackPtr,
            'Abort',
            [$name],
        );
    }

    /**
     * Flag the instantiation of an `*HttpException`.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    private function flagHttpException(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $class  = '';

        for ($i = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true); $i !== false; $i++) {
            $code = $tokens[$i]['code'];

            if ($code === T_STRING) {
                $class = $tokens[$i]['content'];
            } elseif ($code !== T_NS_SEPARATOR) {
                break;
            }
        }

        if (!str_ends_with($class, 'HttpException')) {
            return;
        }

        $phpcsFile->addError(
            'Services must not throw HTTP exceptions ("%s"); throw a domain exception instead.',
            $stackPtr,
            'HttpException',
            [$class],
        );
    }

    /**
     * Determine whether the file's namespace has a `Services` segment.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return bool
     */
    private function isInServiceNamespace(File $phpcsFile): bool
    {
        $tokens    = $phpcsFile->getTokens();
        $namespace = $phpcsFile->findNext(T_NAMESPACE, 0);

        if ($namespace === false) {
            return false;
        }

        for ($i = $namespace + 1; isset($tokens[$i]); $i++) {
            $code = $tokens[$i]['code'];

            if (in_array($code, [T_SEMICOLON, T_OPEN_CURLY_BRACKET], true)) {
                break;
            }

            if ($code === T_STRING && $tokens[$i]['content'] === 'Services') {
                return true;
            }
        }

        return false;
    }
}
