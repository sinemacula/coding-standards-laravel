<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Architecture;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsTestClasses;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesImports;
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
 * that cannot be injected.
 *
 * It also does not fire in a class the framework constructs itself, which has
 * no injection point to use instead: a model factory is reached through
 * `Model::factory()`, so neither its constructor nor `definition()` can be
 * given a collaborator. Helper names and both exempt sets are configurable.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowServiceLocationSniff implements Sniff
{
    use DetectsFunctionCalls;
    use DetectsTestClasses;
    use ResolvesImports;
    use ResolvesNamespace;

    /** @var array<int, string> Container helper functions forbidden inside a class body. */
    public array $helpers = ['app', 'resolve'];

    /** @var array<int, string> Namespace segments marking container-wiring code. */
    public array $wiringNamespaces = ['Providers'];

    /** @var array<int, string> Class-name suffixes marking container-wiring code. */
    public array $wiringSuffixes = ['Provider', 'Registrar'];

    /** @var array<int, string> Base classes (short name) marking container-wiring code. */
    public array $wiringBaseClasses = ['ServiceProvider', 'Registrar'];

    /** @var array<int, string> Base classes the framework constructs, leaving no injection point. */
    public array $uninjectableBaseClasses = ['Illuminate\Database\Eloquent\Factories\Factory'];

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
            || $this->isUninjectableClass($phpcsFile, $stackPtr)
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
     * Whether the enclosing class is one the framework constructs, so there is
     * no constructor to inject through.
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
     * Whether the parent, as written, is one of the configured uninjectable
     * bases.
     *
     * An entry containing a separator is matched against the parent resolved
     * through the file's imports, so a base is identified by the class it
     * actually names rather than by a word other classes happen to end with. A
     * bare entry is matched against the short name, which is how a project
     * names an intermediate base of its own.
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
     * Wiring bases are matched by short name alone: the set includes names such
     * as Registrar that have no one qualified form to compare against.
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
