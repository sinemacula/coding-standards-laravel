<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;
use SineMacula\CodingStandards\Sniffs\Concerns\ResolvesQualifiedNames;

/**
 * Read the attributes declared on a class member.
 *
 * Token-based sniffs cannot resolve inheritance, but they can read the
 * attributes written directly above a declaration. This reports whether a
 * member carries a given attribute by short name (so `#[Override]` and
 * `#[\Override]` both match), across PHP_CodeSniffer 3.x and 4.x name forms.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait ReadsAttributes
{
    use ResolvesQualifiedNames;

    /** @var array<int, int|string> Modifiers that sit between a member and its attributes. */
    private array $attributeModifierTokens = [
        T_WHITESPACE,
        T_PUBLIC,
        T_PROTECTED,
        T_PRIVATE,
        T_STATIC,
        T_ABSTRACT,
        T_FINAL,
        T_READONLY,
    ];

    /**
     * Whether the member at the pointer carries the named attribute.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  string  $name
     * @return bool
     */
    protected function hasAttribute(File $phpcsFile, int $stackPtr, string $name): bool
    {
        return in_array($name, $this->attributeNames($phpcsFile, $stackPtr), true);
    }

    /**
     * The short names of every attribute declared on the member.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return array<int, string>
     */
    private function attributeNames(File $phpcsFile, int $stackPtr): array
    {
        $tokens = $phpcsFile->getTokens();
        $names  = [];
        $ptr    = $stackPtr;

        while (($end = $this->precedingAttributeEnd($phpcsFile, $ptr)) !== null) {
            $opener = (int) $phpcsFile->findPrevious(T_ATTRIBUTE, $end - 1);
            $names  = array_merge($names, $this->attributeGroupNames($tokens, $opener, $end));
            $ptr    = $opener;
        }

        return $names;
    }

    /**
     * The attribute-group end immediately preceding the member, if any.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $ptr
     * @return int|null
     */
    private function precedingAttributeEnd(File $phpcsFile, int $ptr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $prev   = $phpcsFile->findPrevious($this->attributeModifierTokens, $ptr - 1, null, true);

        return $prev !== false && $tokens[$prev]['code'] === T_ATTRIBUTE_END ? $prev : null;
    }

    /**
     * The short names declared in a single attribute group.
     *
     * @param  array<int, array<string, mixed>>  $tokens
     * @param  int  $opener
     * @param  int  $closer
     * @return array<int, string>
     */
    private function attributeGroupNames(array $tokens, int $opener, int $closer): array
    {
        $names = [];
        $i     = $opener + 1;

        while ($i < $closer) {
            if ($tokens[$i]['code'] === T_OPEN_PARENTHESIS) {
                $i = $tokens[$i]['parenthesis_closer'] + 1;

                continue;
            }

            if (in_array($tokens[$i]['code'], $this->nameTokenCodes(), true)) {
                $names[] = $this->attributeShortName($tokens[$i]['content']);
            }

            $i++;
        }

        return $names;
    }

    /**
     * The last segment of a qualified name.
     *
     * @param  string  $name
     * @return string
     */
    private function attributeShortName(string $name): string
    {
        $position = strrpos($name, '\\');

        return $position === false ? $name : substr($name, $position + 1);
    }
}
