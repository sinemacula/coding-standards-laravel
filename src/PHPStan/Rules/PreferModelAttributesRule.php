<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Prefer model attributes over their legacy configuration properties.
 *
 * Eloquent exposes attribute classes (#[Table], #[Hidden], #[Touches]) that
 * replace the matching configuration properties. On a model those properties
 * must be expressed as attributes instead - except $hidden, which stays a
 * property once it lists more than five fields.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class PreferModelAttributesRule implements Rule
{
    /** @var array<string, string> Map of model property name to its attribute. */
    private const array PROPERTIES = [
        'table'   => 'Table',
        'hidden'  => 'Hidden',
        'touches' => 'Touches',
    ];

    /** @var int Maximum fields before the property form is preferred. */
    private const int HIDDEN_LIMIT = 5;

    /**
     * The node type this rule inspects.
     *
     * @return string
     */
    #[\Override]
    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * Flag model properties that have a preferred attribute equivalent.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->extends?->getLast() !== 'Model') {
            return [];
        }

        $errors = [];

        foreach ($node->getProperties() as $property) {
            foreach ($property->props as $item) {
                $error = $this->propertyError($item->name->toString(), $item->default, $item->getStartLine());

                if ($error === null) {
                    continue;
                }

                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * Build the error for a single property declaration, if one applies.
     *
     * @param  string  $name
     * @param  \PhpParser\Node\Expr|null  $default
     * @param  int  $line
     * @return \PHPStan\Rules\RuleError|null
     */
    private function propertyError(string $name, ?Expr $default, int $line): ?RuleError
    {
        if (!isset(self::PROPERTIES[$name])) {
            return null;
        }

        if (
            $name === 'hidden'
            && $default instanceof Array_
            && count($default->items) > self::HIDDEN_LIMIT
        ) {
            return null;
        }

        return RuleErrorBuilder::message(sprintf(
            'Use the #[%s] attribute instead of the $%s property.',
            self::PROPERTIES[$name],
            $name,
        ))->identifier('sineMaculaLaravel.modelAttribute')->line($line)->build();
    }
}
