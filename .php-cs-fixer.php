<?php

declare(strict_types=1);

$config = new PhpCsFixer\Config();

$config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'          => true,
        '@PHP81Migration' => true,
        //Remove when 82
        'simple_to_complex_string_variable' => true,
        // Fruitcake
        //Modernize code
        'array_push'              => true,
        'modernize_strpos'        => true,
        'modernize_types_casting' => true,
        //Align arrays
        'trim_array_spaces'      => true,
        'binary_operator_spaces' => [
            'default'   => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal',
            ],
        ],
        //Casting
        'no_short_bool_cast' => true,
        'cast_spaces'        => true,

        // Class names
        'no_leading_namespace_whitespace' => true,
        'no_unused_imports'               => true,
        'single_space_after_construct'    => true,

        //Remove unneeded code
        'no_unneeded_curly_braces'    => true,
        'no_useless_else'             => true,
        'no_useless_return'           => true,
        'no_extra_blank_lines'        => true,
        'blank_line_before_statement' => true,
        //See full list in https://cs.symfony.com/doc/rules/index.html

    ])
    ->setLineEnding("\n");

return $config;
