<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Controllers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\IdentifiesControllers;

/**
 * Disallow non-REST public actions on controllers.
 *
 * A controller's public methods must be limited to the canonical REST actions
 * (index/show/store/update/destroy/create/edit) or a single `__invoke`; other
 * behaviour belongs in a service or a dedicated controller. Non-public helpers
 * are unaffected. A deliberate exception can be bypassed with a `phpcs:ignore`
 * directive on the method.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowNonRestActionsSniff implements Sniff
{
    use IdentifiesControllers;

    /** @var array<int, string> Method names permitted on a controller. */
    public array $allowed = [
        'index', 'show', 'store', 'update', 'destroy', 'create', 'edit',
        '__invoke', '__construct',
    ];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * Process a method declaration on a controller.
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

        if ($phpcsFile->getMethodProperties($stackPtr)['scope'] !== 'public') {
            return;
        }

        $name = $phpcsFile->getDeclarationName($stackPtr);

        if ($name === null || in_array($name, $this->allowed, true)) {
            return;
        }

        $phpcsFile->addError(
            'Controller action "%s()" is not a canonical REST action; move it to a service or a dedicated controller.',
            $stackPtr,
            'Found',
            [$name],
        );
    }
}
