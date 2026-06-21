<?php

use SineMacula\CodingStandards\PhpCsFixerConfig;

// Filter to the directories that exist; in qlty's sparse pre-commit worktree the
// source trees may be absent, and Finder::in() throws on a missing directory.
$dirs = array_values(array_filter([
    dirname(__DIR__, 2) . '/src',
    dirname(__DIR__, 2) . '/SineMaculaLaravel',
], 'is_dir'));

// This repo's PHP is PHP_CodeSniffer sniff code. The shared rules promote docblock
// @param types to native types, but Sniff::process(File, $stackPtr) leaves $stackPtr
// untyped and PHP fatals if it is narrowed to int, so the promotion is disabled here.
return PhpCsFixerConfig::make($dirs !== [] ? $dirs : [dirname(__DIR__, 2)], [
    'phpdoc_to_param_type' => false,
]);
