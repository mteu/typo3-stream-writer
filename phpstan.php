<?php

declare(strict_types=1);

return [
    'includes' => [
        # 'phpstan-baseline.neon',
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
