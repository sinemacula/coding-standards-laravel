<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Architecture;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesRole;

/**
 * Disallow service location in class bodies.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowServiceLocationSniff implements Sniff
{
    use DetectsFunctionCalls, ResolvesRole;

    /** @var array<int, string> Container helper functions forbidden inside a class body. */
    public array $helpers = ['app', 'resolve'];

    /** @var array<int, string> Namespace segments marking container-wiring code. */
    public array $wiringNamespaces = ['Providers'];

    /** @var array<int, string> Class-name suffixes marking container-wiring code. */
    public array $wiringSuffixes = ['Provider', 'Registrar'];

    /** @var array<int, string> Base classes (short name) marking container-wiring code. */
    public array $wiringBaseClasses = ['ServiceProvider', 'Registrar'];

    /** @var array<int, string> Base classes the framework constructs, leaving no injection point. */
    public array $uninjectableBaseClasses = [
        'Illuminate\Database\Eloquent\Factories\Factory',
        'Illuminate\Http\Resources\Json\JsonResource',
        'Illuminate\Http\Resources\Json\ResourceCollection',
        'SineMacula\ApiToolkit\Http\Resources\ApiResource',
        'SineMacula\ApiToolkit\Http\Resources\ReportResource',
    ];

    /** @var array<string, string> Hook method => comma-separated roles the framework calls it on without the container. */
    public array $uninjectableHooks = [
        'failed' => 'Job,Mailable,Notification',
    ];

    /**
     * Register the tokens this sniff listens for.
     *
     * @return array<int, int|string>
     */
    #[\Override]
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * Process a string (potential call name) token.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if ($this->isInClass($tokens, $stackPtr) === false) {
            return;
        }

        $name = $tokens[$stackPtr]['content'];
        $kind = $this->violationKind($phpcsFile, $stackPtr, $name);

        if ($kind === null || $this->isExempt($phpcsFile, $stackPtr)) {
            return;
        }

        $message = $kind === 'Helper'
            ? 'Service location ("%s()") is not allowed in a class body; inject the dependency instead.'
            : 'Service location ("App::%s()") is not allowed in a class body; inject the dependency instead.';

        $phpcsFile->addError($message, $stackPtr, $kind, [$name]);
    }

    /**
     * Classify the call as a helper, a facade make, or neither.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  string  $name
     * @return string|null
     */
    private function violationKind(File $phpcsFile, int $stackPtr, string $name): ?string
    {
        if (in_array($name, $this->helpers, true) && $this->isFunctionCall($phpcsFile, $stackPtr)) {
            return 'Helper';
        }

        if (($name === 'make' || $name === 'makeWith') && $this->isFacadeMake($phpcsFile, $stackPtr)) {
            return 'Facade';
        }

        return null;
    }

    /**
     * Whether the call is exempt from the rule.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isExempt(File $phpcsFile, int $stackPtr): bool
    {
        return $this->isDynamicResolution($phpcsFile, $stackPtr)
            || $this->isTestFile($phpcsFile)
            || $this->isOutsideInjection($phpcsFile, $stackPtr);
    }

    /**
     * Whether the call sits where injection does not apply.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isOutsideInjection(File $phpcsFile, int $stackPtr): bool
    {
        return $this->isWiringClass($phpcsFile, $stackPtr)
            || $this->isUninjectableClass($phpcsFile, $stackPtr)
            || $this->isUninjectableHook($phpcsFile, $stackPtr);
    }

    /**
     * Determine whether the token sits inside a class, trait or enum.
     *
     * @param  array<int, array<string, mixed>>  $tokens
     * @param  int  $stackPtr
     * @return bool
     */
    private function isInClass(array $tokens, int $stackPtr): bool
    {
        foreach ($tokens[$stackPtr]['conditions'] as $code) {
            if (in_array($code, [T_CLASS, T_TRAIT, T_ENUM, T_ANON_CLASS], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the token is an `App::make()` / `App::makeWith()` call.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isFacadeMake(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $colon  = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($colon === false || $tokens[$colon]['code'] !== T_DOUBLE_COLON) {
            return false;
        }

        $class = $phpcsFile->findPrevious(T_WHITESPACE, $colon - 1, null, true);

        return $class !== false && $tokens[$class]['code'] === T_STRING && $tokens[$class]['content'] === 'App';
    }

    /**
     * Whether the call has a variable argument or none at all.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isDynamicResolution(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $open   = (int) $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);
        $first  = $phpcsFile->findNext(T_WHITESPACE, $open + 1, null, true);

        return $first !== false && in_array($tokens[$first]['code'], [T_VARIABLE, T_CLOSE_PARENTHESIS], true);
    }

    /**
     * Whether the enclosing class wires the container (provider or registrar).
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isWiringClass(File $phpcsFile, int $stackPtr): bool
    {
        foreach ($this->wiringNamespaces as $segment) {
            if ($this->isInNamespacePath($phpcsFile, $segment)) {
                return true;
            }
        }

        $classPtr = $phpcsFile->getCondition($stackPtr, T_CLASS);

        return $classPtr !== false && $this->isWiringDeclaration($phpcsFile, $classPtr);
    }

    /**
     * Whether the enclosing class is one the framework constructs.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isUninjectableClass(File $phpcsFile, int $stackPtr): bool
    {
        $classPtr = $phpcsFile->getCondition($stackPtr, T_CLASS);
        $parent   = $classPtr === false ? false : $phpcsFile->findExtendedClassName($classPtr);

        if ($parent === false) {
            return false;
        }

        return $this->matchesUninjectableBase($phpcsFile, (int) $classPtr, $parent);
    }

    /**
     * Whether the token sits in a hook the framework calls without a container.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isUninjectableHook(File $phpcsFile, int $stackPtr): bool
    {
        $classPtr  = $this->nearestNamedClass($phpcsFile, $stackPtr);
        $methodPtr = $classPtr === null ? null : $this->enclosingMethod($phpcsFile, $stackPtr, $classPtr);

        if ($methodPtr === null) {
            return false;
        }

        $roles = $this->uninjectableHooks[strtolower((string) $phpcsFile->getDeclarationName($methodPtr))] ?? '';

        return in_array($this->resolveRole($phpcsFile, $classPtr), $this->split($roles), true);
    }

    /**
     * Find the innermost class-like scope, if it is a named class.
     *
     * A class nested in a hook is judged on its own.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return int|null
     */
    private function nearestNamedClass(File $phpcsFile, int $stackPtr): ?int
    {
        $conditions = array_reverse($phpcsFile->getTokens()[$stackPtr]['conditions'], true);

        foreach ($conditions as $ptr => $code) {
            if (in_array($code, [T_CLASS, T_ANON_CLASS, T_TRAIT, T_ENUM], true)) {
                return $code === T_CLASS && is_int($ptr) ? $ptr : null;
            }
        }

        return null;
    }

    /**
     * Find the class method the token sits in, if any.
     *
     * The scope nested directly in a class body can only be one of its methods.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @param  int  $classPtr
     * @return int|null
     */
    private function enclosingMethod(File $phpcsFile, int $stackPtr, int $classPtr): ?int
    {
        $owners = array_keys($phpcsFile->getTokens()[$stackPtr]['conditions']);
        $method = $owners[array_search($classPtr, $owners, true) + 1] ?? null;

        return is_int($method) ? $method : null;
    }

    /**
     * Whether the parent is one of the configured uninjectable bases.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @param  string  $parent
     * @return bool
     */
    private function matchesUninjectableBase(File $phpcsFile, int $classPtr, string $parent): bool
    {
        $qualified = $this->qualify(
            $this->importMap($phpcsFile, $classPtr),
            $this->namespaceName($phpcsFile),
            $parent,
        );

        foreach ($this->uninjectableBaseClasses as $base) {
            $matched = str_contains($base, '\\')
                ? str_ends_with('\\' . $qualified, '\\' . ltrim($base, '\\'))
                : $this->shortNameOf($parent) === $base;

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the class declaration is a wiring class by suffix or base class.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return bool
     */
    private function isWiringDeclaration(File $phpcsFile, int $classPtr): bool
    {
        return $this->hasWiringSuffix($phpcsFile->getDeclarationName($classPtr))
            || $this->extendsWiringBase($phpcsFile->findExtendedClassName($classPtr));
    }

    /**
     * Whether the class name ends with a configured wiring suffix.
     *
     * @param  string|null  $name
     * @return bool
     */
    private function hasWiringSuffix(?string $name): bool
    {
        foreach ($this->wiringSuffixes as $suffix) {
            if ($name !== null && str_ends_with($name, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the extended class short name is a configured wiring base.
     *
     * Matched by short name, since `Registrar` has no single qualified form.
     *
     * @param  false|string  $parent
     * @return bool
     */
    private function extendsWiringBase(false|string $parent): bool
    {
        return $parent !== false && in_array($this->shortNameOf($parent), $this->wiringBaseClasses, true);
    }

    /**
     * Reduce a name as written to its trailing segment.
     *
     * @param  string  $name
     * @return string
     */
    private function shortNameOf(string $name): string
    {
        $position = strrpos($name, '\\');

        return $position === false ? $name : substr($name, $position + 1);
    }
}
