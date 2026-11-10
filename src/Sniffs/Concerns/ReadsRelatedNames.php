<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Read the names a class declaration relates to, as written.
 *
 * Collects the parent a class extends, the interfaces it implements, the traits
 * its body uses and the attributes attached to it, each name exactly as the
 * source writes it - short, qualified or fully qualified - so a caller can
 * resolve it through the file's imports.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait ReadsRelatedNames
{
    /** @var array<int, int|string> Token codes that form part of a (possibly qualified) name. */
    private array $nameTokens = [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR];

    /**
     * Collect the parent class a class extends, as written.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return array<int, string>
     */
    private function parentNames(File $phpcsFile, int $classPtr): array
    {
        $extends = $phpcsFile->findExtendedClassName($classPtr);

        return $extends === false ? [] : [$extends];
    }

    /**
     * Collect the interfaces a class implements and the traits it uses, as
     * written.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return array<int, string>
     */
    private function contractNames(File $phpcsFile, int $classPtr): array
    {
        $implements = $phpcsFile->findImplementedInterfaceNames($classPtr);

        return array_merge($implements === false ? [] : $implements, $this->usedTraitNames($phpcsFile, $classPtr));
    }

    /**
     * Collect the names of traits used directly in the class body, as written.
     *
     * A trait `use` is the one whose innermost scope is the class itself, so a
     * closure's `use` and a nested class's traits are left out. Each statement
     * ends at its semicolon, or at the brace opening a conflict-resolution
     * block, whose names are not traits the class uses.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return array<int, string>
     */
    private function usedTraitNames(File $phpcsFile, int $classPtr): array
    {
        $names = [];

        foreach ($phpcsFile->getTokens() as $ptr => $token) {
            if ($token['code'] !== T_USE || array_key_last($token['conditions']) !== $classPtr) {
                continue;
            }

            $end   = $phpcsFile->findNext([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $ptr);
            $names = array_merge($names, $this->namesBetween($phpcsFile, $ptr, $end ?: $ptr));
        }

        return $names;
    }

    /**
     * Collect the names of attributes attached to the class declaration, from
     * the group nearest the declaration outwards.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return array<int, string>
     */
    private function attributeNames(File $phpcsFile, int $classPtr): array
    {
        $tokens = $phpcsFile->getTokens();
        $names  = [];
        $ptr    = $classPtr;

        while (($end = $this->precedingAttributeEnd($phpcsFile, $ptr)) !== null) {
            $ptr   = $tokens[$end]['attribute_opener'];
            $names = array_merge($names, $this->namesBetween($phpcsFile, $ptr, $end));
        }

        return $names;
    }

    /**
     * Find the closer of an attribute group immediately preceding $ptr.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $ptr
     * @return int|null
     */
    private function precedingAttributeEnd(File $phpcsFile, int $ptr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $prev   = $phpcsFile->findPrevious([T_WHITESPACE, T_ABSTRACT, T_FINAL, T_READONLY], $ptr - 1, null, true);

        return $prev !== false && $tokens[$prev]['code'] === T_ATTRIBUTE_END ? $prev : null;
    }

    /**
     * Collect the names written between two pointers, as written, skipping any
     * parenthesised arguments.
     *
     * PHP_CodeSniffer 3.x splits a qualified name into T_STRING/T_NS_SEPARATOR
     * tokens while 4.x keeps it whole, so each contiguous run of name tokens is
     * read back as the one name it spells.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $start
     * @param  int  $end
     * @return array<int, string>
     */
    private function namesBetween(File $phpcsFile, int $start, int $end): array
    {
        $tokens = $phpcsFile->getTokens();
        $names  = [];
        $ptr    = $start;

        while (($ptr = $phpcsFile->findNext([T_OPEN_PARENTHESIS, ...$this->nameTokens], $ptr, $end)) !== false) {
            if ($tokens[$ptr]['code'] === T_OPEN_PARENTHESIS) {
                $ptr = $tokens[$ptr]['parenthesis_closer'];

                continue;
            }

            $after   = $phpcsFile->findNext($this->nameTokens, $ptr, $end, true) ?: $end;
            $names[] = $phpcsFile->getTokensAsString($ptr, $after - $ptr);
            $ptr     = $after;
        }

        return $names;
    }
}
