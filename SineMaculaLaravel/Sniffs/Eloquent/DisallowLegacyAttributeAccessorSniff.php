<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Sniffs\Eloquent;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallow legacy Eloquent accessors and mutators.
 *
 * The legacy `getXAttribute()` / `setXAttribute()` accessor and mutator methods
 * are superseded by `Attribute::make()`. To avoid flagging unrelated methods
 * that merely share the name, two gates must both hold: the declaring class
 * extends an Eloquent model base (Model, Authenticatable, Pivot - configurable,
 * matched on the immediate base by short name), and the signature matches a
 * real accessor (a getter takes no parameters, a setter takes exactly one).
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final class DisallowLegacyAttributeAccessorSniff implements Sniff
{
    /** @var array<int, string> Eloquent model base classes (matched by short name). */
    public array $modelBaseClasses = ['Model', 'Authenticatable', 'Pivot'];

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
     * Process a function declaration token.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $stackPtr
     * @return void
     */
    #[\Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $name = $phpcsFile->getDeclarationName($stackPtr);

        if ($name === null || preg_match('/^(get|set)[A-Z]\w*Attribute$/', $name, $matches) !== 1) {
            return;
        }

        $classPtr = $phpcsFile->getCondition($stackPtr, T_CLASS, false);

        if ($classPtr === false || $this->isModel($phpcsFile, $classPtr) === false) {
            return;
        }

        if (count($phpcsFile->getMethodParameters($stackPtr)) !== ($matches[1] === 'get' ? 0 : 1)) {
            return;
        }

        $phpcsFile->addError(
            'Legacy accessor/mutator "%s()" is not allowed; define the attribute via Attribute::make().',
            $stackPtr,
            'Found',
            [$name],
        );
    }

    /**
     * Whether the class extends a configured Eloquent model base.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $classPtr
     * @return bool
     */
    private function isModel(File $phpcsFile, int $classPtr): bool
    {
        $parent = $phpcsFile->findExtendedClassName($classPtr);

        if ($parent === false) {
            return false;
        }

        $position = strrpos($parent, '\\');
        $short    = $position === false ? $parent : substr($parent, $position + 1);

        return in_array($short, $this->modelBaseClasses, true);
    }
}
