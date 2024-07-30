<?php

declare(strict_types=1);

return [
    'includes' => [
        '.Build/vendor/phpstan/phpstan/conf/bleedingEdge.neon',
        'phpstan-baseline.neon',
    ],
    'parameters' => [
        'level' => 'max',
        'paths' => [
            'Classes',
            'Configuration',
            'Tests',
        ],
    ],
];