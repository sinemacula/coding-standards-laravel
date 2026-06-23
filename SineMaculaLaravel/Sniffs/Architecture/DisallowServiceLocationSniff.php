<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Architecture;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsTestClasses;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesNamespace;

/**
 * Disallow service location in class bodies.
 *
 * Collaborators must be constructor-injected, not pulled from the container at
 * call time. This flags the `app()` / `resolve()` container helpers and the
 * `App::make()` facade inside a class. It targets production code, so it does
 * not fire in test files, in container-wiring classes (service providers and
 * registrars, whose job is to wire the container), or on a dynamic resolution
 * whose argument is a runtime variable rather than a literal class - a factory
 * that cannot be injected. Helper names and wiring markers are configurable.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowServiceLocationSniff implements Sniff
{
    use DetectsFunctionCalls;
    use DetectsTestClasses;
    use ResolvesNamespace;

    /** @var array<int, string> Container helper functions forbidden inside a class body. */
    public array $helpers = ['app', 'resolve'];

    /** @var array<int, string> Namespace segments marking container-wiring code. */
    public array $wiringNamespaces = ['Providers'];

    /** @var array<int, string> Class-name suffixes marking container-wiring code. */
    public array $wiringSuffixes = ['Provider', 'Registrar'];

    /** @var array<int, string> Base classes (short name) marking container-wiring code. */
    public array $wiringBaseClasses = ['ServiceProvider', 'Registrar'];

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
     * Whether the call is exempt: test code, wiring class, or dynamic argument.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return bool
     */
    private function isExempt(File $phpcsFile, int $stackPtr): bool
    {
        return $this->isDynamicResolution($phpcsFile, $stackPtr)
            || $this->isWiringClass($phpcsFile, $stackPtr)
            || $this->isTestFile($phpcsFile);
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
     * Determine whether the string is the `make`/`makeWith` of an `App::`
     * facade call.
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
     * Whether the call resolves a runtime variable, or only fetches the
     * container, which cannot be replaced by injection.
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
     * @param  false|string  $parent
     * @return bool
     */
    private function extendsWiringBase(false|string $parent): bool
    {
        if ($parent === false) {
            return false;
        }

        $position = strrpos($parent, '\\');
        $short    = $position === false ? $parent : substr($parent, $position + 1);

        return in_array($short, $this->wiringBaseClasses, true);
    }
}
