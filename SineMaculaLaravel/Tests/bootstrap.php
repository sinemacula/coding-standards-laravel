<?php

declare(strict_types = 1);

// Loads the constants and token tables PHP_CodeSniffer expects outside its CLI.
use PHP_CodeSniffer\Util\Tokens;
use PhpParser\Lexer;

$root = dirname(__DIR__, 2);

require_once $root . '/vendor/autoload.php';

// php-parser needs integer token IDs; PHP_CodeSniffer would polyfill strings.
class_exists(Lexer::class);

// Composer's PSR-4 map does not cover the PHP_CodeSniffer namespace.
require_once $root . '/vendor/squizlabs/php_codesniffer/autoload.php';

if (defined('PHP_CODESNIFFER_VERBOSITY') === false) {
    define('PHP_CODESNIFFER_VERBOSITY', 0);
}

if (defined('PHP_CODESNIFFER_CBF') === false) {
    define('PHP_CODESNIFFER_CBF', false);
}

// Defines the custom token constants the sniffs reference, such as T_ENUM_CASE.
class_exists(Tokens::class);
