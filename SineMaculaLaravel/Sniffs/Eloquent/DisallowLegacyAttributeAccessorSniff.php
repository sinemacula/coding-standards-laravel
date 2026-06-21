<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Eloquent;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallow legacy Eloquent accessors and mutators.
 *
 * The legacy `getXAttribute()` / `setXAttribute()` accessor and mutator methods
 * are superseded by `Attribute::make()`. This flags any method whose name
 * matches that pattern. The base `getAttribute()` / `setAttribute()` methods
 * are left alone.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowLegacyAttributeAccessorSniff implements Sniff
{
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
     * Process a function declaration token.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $name = $phpcsFile->getDeclarationName($stackPtr);

        if ($name === null) {
            return; // @codeCoverageIgnore
        }

        if (preg_match('/^(get|set)[A-Z]\w*Attribute$/', $name) !== 1) {
            return;
        }

        $phpcsFile->addError(
            'Legacy accessor/mutator "%s()" is not allowed; define the attribute via Attribute::make().',
            $stackPtr,
            'Found',
            [$name],
        );
    }
}
