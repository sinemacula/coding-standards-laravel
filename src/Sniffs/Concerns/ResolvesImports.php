<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Resolve a file's class imports into an alias => qualified-name map.
 *
 * Collects every file-level `use` statement before a given pointer - plain,
 * aliased, comma-separated and group forms - so a short or relative name
 * written elsewhere in the file can be resolved back to the qualified name it
 * imports. Function and constant imports, closure `use` clauses and trait `use`
 * statements inside a class body are all ignored.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait ResolvesImports
{
    /** @var array<int, int|string> Token codes that form part of an imported name. */
    private array $importNameTokens = [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_NS_SEPARATOR];

    /**
     * Build the map of class imports declared before the given pointer.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $before
     * @return array<string, string>
     */
    private function importMap(File $phpcsFile, int $before): array
    {
        $tokens = $phpcsFile->getTokens();
        $map    = [];
        $use    = 0;

        while (($use = $phpcsFile->findNext(T_USE, $use + 1, $before)) !== false) {
            if ($tokens[$use]['conditions'] !== [] || $this->isClosureUse($phpcsFile, $use)) {
                continue;
            }

            $map += $this->importsFrom($phpcsFile, $use);
        }

        return $map;
    }

    /**
     * Whether the `use` at the pointer is a closure's variable-capture clause.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $use
     * @return bool
     */
    private function isClosureUse(File $phpcsFile, int $use): bool
    {
        $tokens = $phpcsFile->getTokens();
        $next   = $phpcsFile->findNext(T_WHITESPACE, $use + 1, null, true);

        return $next !== false && $tokens[$next]['code'] === T_OPEN_PARENTHESIS;
    }

    /**
     * Collect the alias => qualified-name pairs of one import statement.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $use
     * @return array<string, string>
     */
    private function importsFrom(File $phpcsFile, int $use): array
    {
        $tokens = $phpcsFile->getTokens();
        $end    = $phpcsFile->findNext(T_SEMICOLON, $use + 1);
        $end    = $end === false ? count($tokens) : $end;
        $map    = [];
        $state  = ['prefix' => '', 'name' => '', 'alias' => null, 'skip' => false];

        for ($i = $use + 1; $i < $end; $i++) {
            $code = $tokens[$i]['code'];

            if ($code === T_OPEN_USE_GROUP) {
                $state['prefix'] = $state['name'];
                $state['name']   = '';
            } elseif ($code === T_AS) {
                $state['alias'] = '';
            } elseif (in_array($code, $this->importNameTokens, true)) {
                $this->appendNamePart($phpcsFile, $i, $state);
            } elseif ($code === T_COMMA || $code === T_CLOSE_USE_GROUP) {
                $this->commitImport($map, $state);
            }
        }

        $this->commitImport($map, $state);

        return $map;
    }

    /**
     * Append a name token to the entry being parsed, in alias or name position.
     *
     * A leading `function` or `const` keyword - written as a bare word followed
     * by another name rather than a `\` separator - marks a function/constant
     * import, which resolves in a different symbol space and is skipped.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $ptr
     * @param  array{prefix: string, name: string, alias: string|null, skip: bool}  $state
     * @return void
     */
    private function appendNamePart(File $phpcsFile, int $ptr, array &$state): void
    {
        $tokens  = $phpcsFile->getTokens();
        $content = $tokens[$ptr]['content'];

        if (
            $state['name']     === ''
            && $state['alias'] === null
            && in_array(strtolower($content), ['function', 'const'], true)
            && $tokens[$ptr + 1]['code'] !== T_NS_SEPARATOR
        ) {
            $state['skip'] = true;

            return;
        }

        if ($state['alias'] !== null) {
            $state['alias'] .= $content;

            return;
        }

        $state['name'] .= $content;
    }

    /**
     * Commit the entry being parsed to the map and reset for the next one.
     *
     * @param  array<string, string>  $map
     * @param  array{prefix: string, name: string, alias: string|null, skip: bool}  $state
     * @return void
     */
    private function commitImport(array &$map, array &$state): void
    {
        if ($state['name'] !== '' && $state['skip'] === false) {
            $qualified = ltrim($state['prefix'] . $state['name'], '\\');
            $alias     = $state['alias'] ?? $this->importShortName($qualified);

            $map[$alias] = $qualified;
        }

        $state['name']  = '';
        $state['alias'] = null;
        $state['skip']  = false;
    }

    /**
     * Resolve a name as written to its qualified form.
     *
     * A fully-qualified name is taken as-is; otherwise the leading segment is
     * resolved through the given imports, falling back to the enclosing
     * namespace as PHP itself would.
     *
     * @param  array<string, string>  $imports
     * @param  string  $namespace
     * @param  string  $name
     * @return string
     */
    private function qualify(array $imports, string $namespace, string $name): string
    {
        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        $head = strstr($name, '\\', true);
        $head = $head === false ? $name : $head;

        if (isset($imports[$head])) {
            return $imports[$head] . substr($name, strlen($head));
        }

        return $namespace === '' ? $name : $namespace . '\\' . $name;
    }

    /**
     * Reduce a qualified name to its trailing segment.
     *
     * @param  string  $name
     * @return string
     */
    private function importShortName(string $name): string
    {
        $position = strrpos($name, '\\');

        return $position === false ? $name : substr($name, $position + 1);
    }
}
