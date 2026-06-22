<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Require snake_case table and column names in migrations.
 *
 * Database identifiers are snake_case by Laravel convention. Inside a class
 * extending Migration this flags non-snake_case literal names passed to the
 * Schema table calls (create/table/rename/drop) and to the Blueprint column and
 * index methods. Only string-literal name arguments are inspected - value
 * arguments (an enum's cases, a column default) and dynamic names are left
 * alone. Digits are permitted (line_1, oauth2); only casing is enforced.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\Class_>
 */
final class RequireSnakeCaseSchemaRule implements Rule
{
    /** @var array<int, string> Schema calls whose string arguments name tables. */
    private const array TABLE_METHODS = ['create', 'table', 'rename', 'drop', 'dropIfExists'];

    /** @var array<int, string> Blueprint methods whose first argument names columns. */
    private const array COLUMN_METHODS = [
        'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'date', 'dateTime',
        'dateTimeTz', 'decimal', 'double', 'enum', 'float', 'foreignId', 'foreignUlid',
        'foreignUuid', 'geometry', 'increments', 'integer', 'ipAddress', 'json', 'jsonb',
        'longText', 'macAddress', 'mediumIncrements', 'mediumInteger', 'mediumText', 'morphs',
        'multiPoint', 'nullableMorphs', 'point', 'set', 'smallIncrements', 'smallInteger',
        'string', 'text', 'time', 'timeTz', 'timestamp', 'timestampTz', 'tinyIncrements',
        'tinyInteger', 'tinyText', 'ulid', 'unsignedBigInteger', 'unsignedDecimal',
        'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger',
        'uuid', 'year', 'index', 'unique', 'primary', 'fullText', 'spatialIndex',
    ];

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
     * Flag non-snake_case table and column names declared in a migration.
     *
     * @param  \PhpParser\Node\Stmt\Class_  $node
     * @param  \PHPStan\Analyser\Scope  $scope
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    #[\Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node->extends?->getLast() !== 'Migration') {
            return [];
        }

        $finder = new NodeFinder;
        $errors = [];

        foreach ($finder->findInstanceOf($node, StaticCall::class) as $call) {
            $errors = array_merge($errors, $this->schemaErrors($call));
        }

        foreach ($finder->findInstanceOf($node, MethodCall::class) as $call) {
            $errors = array_merge($errors, $this->blueprintErrors($call));
        }

        return $errors;
    }

    /**
     * Collect errors for a Schema::* table call's name arguments.
     *
     * @param  \PhpParser\Node\Expr\StaticCall  $call
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function schemaErrors(StaticCall $call): array
    {
        $method = $this->methodName($call->name);

        if (!$call->class instanceof Name || $call->class->getLast() !== 'Schema') {
            return [];
        }

        if ($method === null || in_array($method, self::TABLE_METHODS, true) === false) {
            return [];
        }

        $positions = $method === 'rename' ? [0, 1] : [0];
        $strings   = array_filter(array_map(fn (int $i): ?String_ => $this->stringArg($call, $i), $positions));

        return $this->nameErrors($strings, 'Table');
    }

    /**
     * Collect errors for a Blueprint column or index method's name arguments.
     *
     * @param  \PhpParser\Node\Expr\MethodCall  $call
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function blueprintErrors(MethodCall $call): array
    {
        $method = $this->methodName($call->name);

        if ($method === null || in_array($method, self::COLUMN_METHODS, true) === false) {
            return [];
        }

        return $this->nameErrors($this->firstArgStrings($call), 'Column');
    }

    /**
     * The string literals of a call's first argument (one string or an array).
     *
     * @param  \PhpParser\Node\Expr\MethodCall  $call
     * @return array<int, \PhpParser\Node\Scalar\String_>
     */
    private function firstArgStrings(MethodCall $call): array
    {
        $arg = $call->args[0] ?? null;

        if (!$arg instanceof Arg) {
            return [];
        }

        if ($arg->value instanceof String_) {
            return [$arg->value];
        }

        return $arg->value instanceof Array_ ? $this->arrayStrings($arg->value) : [];
    }

    /**
     * The string-literal items of an array literal.
     *
     * @param  \PhpParser\Node\Expr\Array_  $array
     * @return array<int, \PhpParser\Node\Scalar\String_>
     */
    private function arrayStrings(Array_ $array): array
    {
        $strings = [];

        foreach ($array->items as $item) {
            if (!$item->value instanceof String_) {
                continue;
            }

            $strings[] = $item->value;
        }

        return $strings;
    }

    /**
     * Build an error for each non-snake_case name among the given strings.
     *
     * @param  array<int, \PhpParser\Node\Scalar\String_>  $strings
     * @param  string  $kind
     * @return array<int, \PHPStan\Rules\RuleError>
     */
    private function nameErrors(array $strings, string $kind): array
    {
        $errors = [];

        foreach ($strings as $string) {
            if ($this->isSnakeCase($string->value)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                '%s name "%s" must use snake_case.',
                $kind,
                $string->value,
            ))->identifier('sineMaculaLaravel.schemaNaming')->line($string->getStartLine())->build();
        }

        return $errors;
    }

    /**
     * The string name of a call's method identifier, if it is one.
     *
     * @param  \PhpParser\Node\Expr|\PhpParser\Node\Identifier  $name
     * @return string|null
     */
    private function methodName(Identifier|Node\Expr $name): ?string
    {
        return $name instanceof Identifier ? $name->toString() : null;
    }

    /**
     * The string literal at a call's argument position, if it is one.
     *
     * @param  \PhpParser\Node\Expr\StaticCall  $call
     * @param  int  $index
     * @return \PhpParser\Node\Scalar\String_|null
     */
    private function stringArg(StaticCall $call, int $index): ?String_
    {
        $arg = $call->args[$index] ?? null;

        return $arg instanceof Arg && $arg->value instanceof String_ ? $arg->value : null;
    }

    /**
     * Whether a name is lower snake_case, optionally with digits.
     *
     * @param  string  $value
     * @return bool
     */
    private function isSnakeCase(string $value): bool
    {
        return preg_match('/^[a-z][a-z0-9]*(_[a-z0-9]+)*$/', $value) === 1;
    }
}
