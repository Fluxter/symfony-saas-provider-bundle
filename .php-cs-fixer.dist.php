<?php

$header = <<<'EOF'
This file is part of the SaasProviderBundle package.
(c) Fluxter <http://fluxter.net/>
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'header_comment' => ['header' => $header],
        'linebreak_after_opening_tag' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_construct' => true,
        'php_unit_strict' => true,
        'phpdoc_no_empty_return' => false,
        'concat_space' => ['spacing' => 'one'],
        'no_php4_constructor' => true,
        'no_useless_else' => true,
        'no_leading_namespace_whitespace' => true,
        'echo_tag_syntax' => ['format' => 'long'],
        'no_spaces_around_offset' => true,
        'no_spaces_after_function_name' => true,
        'no_unused_imports' => true,
        'single_quote' => true,
    ])
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
