<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Concerns;

use PHP_CodeSniffer\Files\File;

/**
 * Resolve the Laravel "role" a class plays, for the structure sniffs.
 *
 * Detection is identity-first: a class is matched to at most one role by what
 * it extends, implements, uses or is attributed with - resolved against a
 * configurable list of identity short names - and only then by a tightly-scoped
 * location fallback (concrete classes, recursive, minus exempt sub-namespaces).
 * A class with neither is unconstrained. An `@role-exempt` docblock tag or a
 * `#[NotARole]` attribute opts a class out entirely.
 *
 * The role table and every list are public properties so a consuming ruleset
 * can override or extend them.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
trait ResolvesRole
{
    use ResolvesNamespace;

    /** @var array<string, string> Role => comma-separated identity short names. */
    public array $roleIdentities = [
        'Controller'      => 'Controller',
        'Model'           => 'Model,Authenticatable,Pivot',
        'ServiceProvider' => 'ServiceProvider',
        'FormRequest'     => 'FormRequest',
        'Resource'        => 'JsonResource',
        'Command'         => 'Command',
        'Job'             => 'ShouldQueue,Dispatchable',
        'Mailable'        => 'Mailable',
        'Notification'    => 'Notification',
        'Cast'            => 'CastsAttributes',
        'Rule'            => 'ValidationRule',
    ];

    /** @var array<string, string> Role => comma-separated namespace location path(s). */
    public array $roleLocations = [
        'Controller'      => 'Http\Controllers',
        'Model'           => 'Models',
        'ServiceProvider' => 'Providers',
        'FormRequest'     => 'Http\Requests',
        'Resource'        => 'Http\Resources',
        'Policy'          => 'Policies',
        'Command'         => 'Console\Commands',
        'Job'             => 'Jobs',
        'Listener'        => 'Listeners',
        'Event'           => 'Events',
        'Mailable'        => 'Mail',
        'Notification'    => 'Notifications',
        'Middleware'      => 'Http\Middleware',
        'Cast'            => 'Casts',
        'Rule'            => 'Rules',
    ];

    /** @var array<int, string> Sub-namespace segments the location fallback ignores. */
    public array $exemptNamespaces = [
        'Concerns', 'Support', 'Contracts', 'Enums', 'Casts', 'Builders', 'Traits', 'Exceptions',
    ];

    /** @var array<int, int|string> Token codes that form a (possibly qualified) name. */
    private array $nameTokens = [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED];

    /**
     * Resolve the single role a class plays, or null when it has none.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return string|null
     */
    protected function resolveRole(File $phpcsFile, int $classPtr): ?string
    {
        if ($this->isRoleExempt($phpcsFile, $classPtr)) {
            return null;
        }

        return $this->roleByIdentity($phpcsFile, $classPtr)
            ?? $this->roleByLocation($phpcsFile, $classPtr);
    }

    /**
     * Resolve a role from what the class extends, implements, uses or carries.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return string|null
     */
    protected function roleByIdentity(File $phpcsFile, int $classPtr): ?string
    {
        $names = $this->identityNames($phpcsFile, $classPtr);

        foreach ($this->roleIdentities as $role => $identities) {
            if (array_intersect($this->split($identities), $names) !== []) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Resolve a role from the class's location, as a fallback to identity.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return string|null
     */
    protected function roleByLocation(File $phpcsFile, int $classPtr): ?string
    {
        if ($phpcsFile->getClassProperties($classPtr)['is_abstract'] !== false) {
            return null;
        }

        foreach ($this->roleLocations as $role => $paths) {
            if ($this->matchesLocation($phpcsFile, $paths)) {
                return $role;
            }
        }

        return null;
    }

    /**
     * Whether the file's namespace matches one of the role's location paths.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  string  $paths
     * @return bool
     */
    private function matchesLocation(File $phpcsFile, string $paths): bool
    {
        foreach ($this->split($paths) as $path) {
            if ($this->isInNamespacePath($phpcsFile, $path) && !$this->isInExemptNamespace($phpcsFile, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Collect the identity short names a class declares.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return array<int, string>
     */
    private function identityNames(File $phpcsFile, int $classPtr): array
    {
        $names      = [];
        $extends    = $phpcsFile->findExtendedClassName($classPtr);
        $implements = $phpcsFile->findImplementedInterfaceNames($classPtr);

        if ($extends !== false) {
            $names[] = $this->shortName($extends);
        }

        foreach ($implements === false ? [] : $implements as $interface) {
            $names[] = $this->shortName($interface);
        }

        return array_merge($names, $this->usedTraitNames($phpcsFile, $classPtr), $this->attributeNames($phpcsFile, $classPtr));
    }

    /**
     * Collect the short names of traits used directly in the class body.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return array<int, string>
     */
    private function usedTraitNames(File $phpcsFile, int $classPtr): array
    {
        $tokens = $phpcsFile->getTokens();
        $closer = $tokens[$classPtr]['scope_closer'] ?? $classPtr;
        $names  = [];

        for ($i = ($tokens[$classPtr]['scope_opener'] ?? $classPtr) + 1; $i < $closer; $i++) {
            if ($tokens[$i]['code'] !== T_USE || array_key_last($tokens[$i]['conditions']) !== $classPtr) {
                continue;
            }

            $names = array_merge($names, $this->namesUntil($phpcsFile, $i, [T_SEMICOLON, T_OPEN_CURLY_BRACKET]));
        }

        return $names;
    }

    /**
     * Collect the names of attributes attached to the class declaration.
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
            $opener = (int) $phpcsFile->findPrevious(T_ATTRIBUTE, $end - 1);
            $names  = array_merge($names, $this->attributeGroupNames($tokens, $opener, $end));
            $ptr    = $opener;
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
     * Collect the attribute names within one attribute group, skipping args.
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

            if (in_array($tokens[$i]['code'], $this->nameTokens, true)) {
                $names[] = $this->shortName($tokens[$i]['content']);
            }

            $i++;
        }

        return $names;
    }

    /**
     * Collect the short names between a pointer and the next of $stops.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $from
     * @param  array<int, int|string>  $stops
     * @return array<int, string>
     */
    private function namesUntil(File $phpcsFile, int $from, array $stops): array
    {
        $tokens = $phpcsFile->getTokens();
        $end    = (int) $phpcsFile->findNext($stops, $from + 1);
        $names  = [];

        for ($i = $from + 1; $i < $end; $i++) {
            if (!in_array($tokens[$i]['code'], $this->nameTokens, true)) {
                continue;
            }

            $names[] = $this->shortName($tokens[$i]['content']);
        }

        return $names;
    }

    /**
     * Whether the class is opted out via `@role-exempt` or `#[NotARole]`.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return bool
     */
    private function isRoleExempt(File $phpcsFile, int $classPtr): bool
    {
        return in_array('NotARole', $this->attributeNames($phpcsFile, $classPtr), true)
            || $this->hasRoleExemptTag($phpcsFile, $classPtr);
    }

    /**
     * Whether the class docblock carries an `@role-exempt` tag.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return bool
     */
    private function hasRoleExemptTag(File $phpcsFile, int $classPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $before = $phpcsFile->findPrevious([T_WHITESPACE, T_ABSTRACT, T_FINAL, T_READONLY], $classPtr - 1, null, true);

        if ($before === false || $tokens[$before]['code'] !== T_DOC_COMMENT_CLOSE_TAG) {
            return false;
        }

        for ($i = $tokens[$before]['comment_opener']; $i < $before; $i++) {
            if ($tokens[$i]['code'] === T_DOC_COMMENT_TAG && strtolower($tokens[$i]['content']) === '@role-exempt') {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the class sits in an exempt sub-namespace other than its role's.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  string  $rolePath
     * @return bool
     */
    private function isInExemptNamespace(File $phpcsFile, string $rolePath): bool
    {
        $own = explode('\\', $rolePath);

        foreach ($this->exemptNamespaces as $segment) {
            if (!in_array($segment, $own, true) && $this->isInNamespacePath($phpcsFile, $segment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Split a comma-separated, trimmed configuration string into a list.
     *
     * @param  string  $value
     * @return array<int, string>
     */
    private function split(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $part): bool => $part !== ''));
    }

    /**
     * Reduce a possibly-qualified name to its trailing segment.
     *
     * @param  string  $name
     * @return string
     */
    private function shortName(string $name): string
    {
        $position = strrpos($name, '\\');

        return $position === false ? $name : substr($name, $position + 1);
    }
}
