<?php

use SineMacula\CodingStandards\PhpCsFixerConfig;

// The pre-commit worktree is sparse, and Finder::in() throws on a missing directory.
$dirs = array_values(array_filter([
    dirname(__DIR__, 2) . '/src',
    dirname(__DIR__, 2) . '/SineMaculaLaravel',
], 'is_dir'));

// Sniff::process() must leave $stackPtr untyped, and class_keyword would rewrite
// the sniffs' class-name suffix strings to ::class.
return PhpCsFixerConfig::make($dirs !== [] ? $dirs : [dirname(__DIR__, 2)], [
    'phpdoc_to_param_type' => false,
    'class_keyword'        => false,
]);
