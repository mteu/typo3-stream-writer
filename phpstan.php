<?php

declare(strict_types=1);

$configuration = [
    'parameters' => [
        'ignoreErrors' => [],
    ],
];

$configuration = require 'phpstan-baseline.php';

$configuration['parameters']['level'] = 'max';

$configuration['parameters']['paths'] = [
    'Classes',
    'Configuration',
    'Tests',
];

$configuration['parameters']['docblock'] = [
    'copyrightIdentifier' => 'Copyright (C) ',
    'requiredLicenseIdentifier' => 'GPL-3.0',
];

$configuration['parameters']['ergebnis'] = [
    'noExtends' => [
        'classesAllowedToBeExtended' => [
            \TYPO3\CMS\Core\Log\Writer\AbstractWriter::class,
        ],
    ],
];

if (count($configuration['parameters']['ignoreErrors']) > 0) {
    foreach ($configuration['parameters']['ignoreErrors'] as $baselineIgnore) {
        $ignoreErrors[] = $baselineIgnore['message'];
    }
}

$configuration['parameters']['ignoreErrors'] = array_unique($ignoreErrors);

return $configuration;
