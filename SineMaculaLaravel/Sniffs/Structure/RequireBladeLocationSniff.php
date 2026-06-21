<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Structure;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Require Blade templates to live under a views directory.
 *
 * A `*.blade.php` file must sit somewhere under a `resources/views` (standard
 * Laravel) or `Resources/views` (module) directory. The check is purely on the
 * file path, so it runs once per file on its first token.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class RequireBladeLocationSniff implements Sniff
{
    /** @var string The file-name suffix that marks a Blade template. */
    public string $extension = '.blade.php';

    /** @var array<int, string> The directory paths a template may live under. */
    public array $directories = ['resources/views', 'Resources/views'];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_OPEN_TAG, T_INLINE_HTML];
    }

    /**
     * Process the first token of a file.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $path = $phpcsFile->getFilename();

        if ($stackPtr !== 0 || !str_ends_with($path, $this->extension)) {
            return;
        }

        foreach ($this->directories as $directory) {
            if (str_contains($path, '/' . $directory . '/')) {
                return;
            }
        }

        $phpcsFile->addError(
            'A Blade template must live under a "%s" directory.',
            $stackPtr,
            'Misplaced',
            [$this->directories[0]],
        );
    }
}
