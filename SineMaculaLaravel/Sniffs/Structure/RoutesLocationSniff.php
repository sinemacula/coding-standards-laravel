<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Structure;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Require the routes file to sit at the root of an Http directory.
 *
 * A module's route file does not have to exist, but if it does it belongs
 * directly inside the module's `Http` directory (not a subdirectory of it, and
 * not anywhere else).
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class RoutesLocationSniff implements Sniff
{
    /** @var string The file name this sniff governs. */
    public string $filename = 'routes.php';

    /** @var string The directory the file must sit directly inside. */
    public string $directory = 'Http';

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_OPEN_TAG];
    }

    /**
     * Process the start of a file.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $path = $phpcsFile->getFilename();

        if (basename($path) !== $this->filename) {
            return;
        }

        if (basename(dirname($path)) === $this->directory) {
            return;
        }

        $phpcsFile->addError(
            'A "%s" file must live directly inside the "%s" directory.',
            $stackPtr,
            'Misplaced',
            [$this->filename, $this->directory],
        );
    }
}
