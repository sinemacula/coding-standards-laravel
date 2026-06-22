<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Controllers;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ReadsDocblockTags;

/**
 * Disallow non-REST public actions on controllers.
 *
 * Routes live in `routes/*.php`, so the sniff infers which methods are actions:
 * a candidate action is a public, non-static instance method on a concrete
 * `*Controller`. Static methods, abstract classes/methods, the constructor and
 * framework overrides (middleware, authorize, callAction) are auto-exempt, so
 * the directive is rarely needed. A candidate action whose name is not in the
 * canonical set (the Laravel resource verbs plus `__invoke`) is flagged.
 *
 * Two escape hatches, kept distinct for auditability:
 * - `@utility` (class or method) - not a routable action (helpers, a base
 *   controller's machinery); a class-level tag exempts the whole class.
 * - `@non-rest-action` (method) - a deliberate non-CRUD route action
 *   (streaming, SSE, the rare genuine webhook); allowed but explicit.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowNonRestActionsSniff implements Sniff
{
    use ReadsDocblockTags;

    /** @var array<int, string> Canonical controller action method names. */
    public array $actions = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy', '__invoke',
    ];

    /** @var array<int, string> Methods auto-exempt as the constructor or framework overrides. */
    public array $exemptMethods = [
        '__construct', 'middleware', 'authorize', 'callAction',
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
        $name     = $phpcsFile->getDeclarationName($stackPtr);
        $classPtr = $phpcsFile->getCondition($stackPtr, T_CLASS, false);

        if ($name === null || $classPtr === false || $this->isExempt($phpcsFile, $classPtr, $stackPtr, $name)) {
            return;
        }

        if (in_array($name, $this->actions, true) || $this->hasDocblockTag($phpcsFile, $stackPtr, '@non-rest-action')) {
            return;
        }

        $phpcsFile->addError(
            'Controller action "%s()" is not a canonical REST action; move it to a service, '
            . 'or mark it @non-rest-action (a deliberate route action) or @utility (not an action).',
            $stackPtr,
            'Found',
            [$name],
        );
    }

    /**
     * Whether the method is not a candidate action and so is exempt.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @param  int  $stackPtr
     * @param  string  $name
     * @return bool
     */
    private function isExempt(File $phpcsFile, int $classPtr, int $stackPtr, string $name): bool
    {
        return $this->isExemptClass($phpcsFile, $classPtr) || $this->isExemptMethod($phpcsFile, $stackPtr, $name);
    }

    /**
     * Whether the enclosing class is not a concrete, routable controller.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return bool
     */
    private function isExemptClass(File $phpcsFile, int $classPtr): bool
    {
        return str_ends_with((string) $phpcsFile->getDeclarationName($classPtr), 'Controller') === false
            || $phpcsFile->getClassProperties($classPtr)['is_abstract'] !== false
            || $this->hasDocblockTag($phpcsFile, $classPtr, '@utility');
    }

    /**
     * Whether the method itself is not a candidate action.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  string  $name
     * @return bool
     */
    private function isExemptMethod(File $phpcsFile, int $stackPtr, string $name): bool
    {
        $properties = $phpcsFile->getMethodProperties($stackPtr);

        return $properties['scope']     !== 'public'
            || $properties['is_static'] !== false
            || in_array($name, $this->exemptMethods, true)
            || $this->hasDocblockTag($phpcsFile, $stackPtr, '@utility');
    }
}
