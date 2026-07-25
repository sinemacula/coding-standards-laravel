<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Sniffs\Routing;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Parse the `Route::verb(...)` registrations in a file into RouteCall values.
 *
 * Reads each facade call's verb, literal URI, controller action and chained
 * modifiers, resolving them to the resource base, action and member parameter a
 * collapse decision needs. Calls that are not a `[Controller::class, 'method']`
 * dispatch with a literal URI - closures, invokables, dynamic URIs, existing
 * apiResource registrations - are skipped, as they cannot form a resource.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
final readonly class RouteParser
{
    /** @var array<string, string> Collection verb => resource action (URI is the bare base). */
    private const array COLLECTION_ACTIONS = ['get' => 'index', 'post' => 'store'];

    /** @var array<string, string> Member verb => resource action (URI is base/{param}). */
    private const array MEMBER_ACTIONS = ['get' => 'show', 'put' => 'update', 'patch' => 'update', 'delete' => 'destroy'];

    /**
     * @param  array<int, string>  $collapsibleModifiers
     * @return void
     */
    public function __construct(

        /** @var array<int, string> Chained modifiers a resource registration can still express. */
        private array $collapsibleModifiers,
    ) {}

    /**
     * Parse every `Route::verb(...)` registration in the file.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @return array<int, \SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteCall>
     */
    public function parse(File $phpcsFile): array
    {
        $tokens = $phpcsFile->getTokens();
        $count  = count($tokens);
        $routes = [];
        $i      = 0;

        while ($i < $count) {
            $open = $this->routeCallOpener($phpcsFile, $i);

            if ($open === null) {
                $i++;

                continue;
            }

            $route = $this->buildRoute($phpcsFile, $i, $open);

            if ($route !== null) {
                $routes[] = $route;
            }

            $i = $tokens[$open]['parenthesis_closer'] + 1;
        }

        return $routes;
    }

    /**
     * The opening parenthesis of a `Route::verb(` head at the pointer, if any.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $ptr
     * @return int|null
     */
    private function routeCallOpener(File $phpcsFile, int $ptr): ?int
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$ptr]['code'] !== T_STRING || $tokens[$ptr]['content'] !== 'Route') {
            return null;
        }

        $colon = $this->after($phpcsFile, $ptr);
        $verb  = $colon === null || $tokens[$colon]['code'] !== T_DOUBLE_COLON ? null : $this->after($phpcsFile, $colon);
        $open  = $verb  === null || $tokens[$verb]['code']  !== T_STRING ? null : $this->after($phpcsFile, $verb);

        return $open !== null && $tokens[$open]['code'] === T_OPEN_PARENTHESIS ? $open : null;
    }

    /**
     * Build a route from its facade pointer and the call's opening parenthesis.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $facade
     * @param  int  $open
     * @return \SineMacula\CodingStandardsLaravel\Sniffs\Routing\RouteCall|null
     */
    private function buildRoute(File $phpcsFile, int $facade, int $open): ?RouteCall
    {
        $tokens  = $phpcsFile->getTokens();
        $colon   = $this->after($phpcsFile, $facade);
        $verbPtr = $colon === null ? null : $this->after($phpcsFile, $colon);
        $close   = $tokens[$open]['parenthesis_closer'];
        $uri     = $this->uriArgument($phpcsFile, $open, $close);
        $comma   = $this->topLevelComma($phpcsFile, $open, $close);

        if ($verbPtr === null || $uri === null || $comma === null) {
            return null;
        }

        $verb = strtolower($tokens[$verbPtr]['content']);

        $controller = $this->actionController($phpcsFile, $comma);

        if ($controller === null) {
            return null;
        }

        [$base, $action, $param] = $this->classify($uri, $verb, $this->actionMethod($phpcsFile, $comma));

        if ($action !== null && !$this->modifiersCollapsible($phpcsFile, $close)) {
            $action = null;
        }

        return new RouteCall($this->enclosingScope($phpcsFile, $facade), $controller, $base, $action, $param, $facade, $tokens[$facade]['line']);
    }

    /**
     * The literal URI of a call's first argument, or null when not a plain
     * string.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $open
     * @param  int  $close
     * @return string|null
     */
    private function uriArgument(File $phpcsFile, int $open, int $close): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $first  = $this->after($phpcsFile, $open);

        if ($first === null || $tokens[$first]['code'] !== T_CONSTANT_ENCAPSED_STRING) {
            return null;
        }

        $after = $this->after($phpcsFile, $first);

        if ($after === null || ($tokens[$after]['code'] !== T_COMMA && $after !== $close)) {
            return null;
        }

        return trim($tokens[$first]['content'], '"\'');
    }

    /**
     * The controller short name of a `[Controller::class, 'method']` action.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $comma
     * @return string|null
     */
    private function actionController(File $phpcsFile, int $comma): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $array  = $this->after($phpcsFile, $comma);

        if ($array === null || $tokens[$array]['code'] !== T_OPEN_SHORT_ARRAY) {
            return null;
        }

        for ($i = $array + 1; $i < $tokens[$array]['bracket_closer']; $i++) {
            if ($this->isClassConstant($phpcsFile, $i)) {
                return $tokens[$i]['content'];
            }
        }

        return null;
    }

    /**
     * The method name of a `[Controller::class, 'method']` action, if present.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $comma
     * @return string|null
     */
    private function actionMethod(File $phpcsFile, int $comma): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $array  = $this->after($phpcsFile, $comma);

        if ($array === null || $tokens[$array]['code'] !== T_OPEN_SHORT_ARRAY) {
            return null;
        }

        $string = $phpcsFile->findNext(T_CONSTANT_ENCAPSED_STRING, $array + 1, $tokens[$array]['bracket_closer']);

        return $string === false ? null : trim($tokens[$string]['content'], '"\'');
    }

    /**
     * Whether the token begins a `Controller::class` constant reference.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $ptr
     * @return bool
     */
    private function isClassConstant(File $phpcsFile, int $ptr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $colon  = $this->after($phpcsFile, $ptr);
        $class  = $colon === null ? null : $this->after($phpcsFile, $colon);

        return $tokens[$ptr]['code']                         === T_STRING
            && $colon !== null && $tokens[$colon]['code']    === T_DOUBLE_COLON
            && $class !== null && $tokens[$class]['content'] === 'class';
    }

    /**
     * Whether every chained modifier after the call is resource-expressible.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $close
     * @return bool
     */
    private function modifiersCollapsible(File $phpcsFile, int $close): bool
    {
        $tokens = $phpcsFile->getTokens();
        $ptr    = $this->after($phpcsFile, $close);

        while ($ptr !== null && in_array($tokens[$ptr]['code'], [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], true)) {
            $name = $this->after($phpcsFile, $ptr);

            if ($name === null || !in_array($tokens[$name]['content'], $this->collapsibleModifiers, true)) {
                return false;
            }

            $open = $this->after($phpcsFile, $name);

            if ($open === null || $tokens[$open]['code'] !== T_OPEN_PARENTHESIS) {
                return false;
            }

            $ptr = $this->after($phpcsFile, $tokens[$open]['parenthesis_closer']);
        }

        return true;
    }

    /**
     * Resolve a URI and verb to its resource base, action and member parameter.
     *
     * @param  string  $uri
     * @param  string  $verb
     * @param  string|null  $method
     * @return array{0: string, 1: string|null, 2: string|null}
     */
    private function classify(string $uri, string $verb, ?string $method): array
    {
        $segments = explode('/', trim($uri, '/'));
        $base     = $segments[0];

        if (count($segments) === 1 && $base !== '' && !str_contains($base, '{')) {
            return [$base, $this->actionFor(self::COLLECTION_ACTIONS, $verb, $method), null];
        }

        if (count($segments) === 2 && str_starts_with($segments[1], '{') && str_ends_with($segments[1], '}')) {
            return [$base, $this->actionFor(self::MEMBER_ACTIONS, $verb, $method), $this->parameter($segments[1])];
        }

        return [$base, null, null];
    }

    /**
     * The resource action for a verb, but only when the method matches the
     * action name.
     *
     * @param  array<string, string>  $actions
     * @param  string  $verb
     * @param  string|null  $method
     * @return string|null
     */
    private function actionFor(array $actions, string $verb, ?string $method): ?string
    {
        $action = $actions[$verb] ?? null;

        return $action !== null && $method === $action ? $action : null;
    }

    /**
     * The parameter name inside a `{param}` segment, without cast or optional
     * markers.
     *
     * @param  string  $segment
     * @return string
     */
    private function parameter(string $segment): string
    {
        return rtrim(explode(':', trim($segment, '{}'))[0], '?');
    }

    /**
     * The token pointer of the innermost enclosing group closure, or 0.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $ptr
     * @return int
     */
    private function enclosingScope(File $phpcsFile, int $ptr): int
    {
        $scope = 0;

        foreach ($phpcsFile->getTokens()[$ptr]['conditions'] as $opener => $code) {
            if ($code !== T_CLOSURE) {
                continue;
            }

            $scope = $opener;
        }

        return $scope;
    }

    /**
     * The first comma at the top level of a call's parentheses, if any.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $open
     * @param  int  $close
     * @return int|null
     */
    private function topLevelComma(File $phpcsFile, int $open, int $close): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $depth  = 0;

        for ($i = $open + 1; $i < $close; $i++) {
            $code = $tokens[$i]['code'];

            if (in_array($code, [T_OPEN_PARENTHESIS, T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET], true)) {
                $depth++;
            } elseif (in_array($code, [T_CLOSE_PARENTHESIS, T_CLOSE_SHORT_ARRAY, T_CLOSE_SQUARE_BRACKET], true)) {
                $depth--;
            } elseif ($code === T_COMMA && $depth === 0) {
                return $i;
            }
        }

        return null;
    }

    /**
     * The next non-empty token strictly after the pointer, if any.
     *
     * @param  \PHP_CodeSniffer\Files\File  $phpcsFile
     * @param  int  $ptr
     * @return int|null
     */
    private function after(File $phpcsFile, int $ptr): ?int
    {
        $found = $phpcsFile->findNext(Tokens::$emptyTokens, $ptr + 1, null, true);

        return $found === false ? null : $found;
    }
}
