<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Read docblock tags attached to a class or method declaration.
 *
 * Mirrors the base @inheritable detection: walk back over the declaration's
 * modifiers to its docblock and look for a given tag. Works for both class and
 * method pointers, so a sniff can offer docblock escape hatches.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait ReadsDocblockTags
{
    /**
     * Whether the declaration at the pointer carries the given docblock tag.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  string  $tag
     * @return bool
     */
    protected function hasDocblockTag(File $phpcsFile, int $stackPtr, string $tag): bool
    {
        $tokens = $phpcsFile->getTokens();
        $before = $phpcsFile->findPrevious(
            [T_WHITESPACE, T_ABSTRACT, T_FINAL, T_READONLY, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC],
            $stackPtr - 1,
            null,
            true,
        );

        if ($before === false || $tokens[$before]['code'] !== T_DOC_COMMENT_CLOSE_TAG) {
            return false;
        }

        for ($i = $tokens[$before]['comment_opener']; $i < $before; $i++) {
            if ($tokens[$i]['code'] === T_DOC_COMMENT_TAG && strtolower($tokens[$i]['content']) === $tag) {
                return true;
            }
        }

        return false;
    }
}
