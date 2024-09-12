<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "mteu/typo3-stream-writer".
 *
 * Copyright (C) 2024 Martin Adler <mteu@mailbox.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

$configuration = [
    'parameters' => [
        'ignoreErrors' => require 'phpstan-baseline.php',
    ],
];

$configuration['parameters']['level'] = 'max';

$configuration['parameters']['paths'] = [
    'Classes',
    'Configuration',
    'Tests',
];

$configuration['parameters']['treatPhpDocTypesAsCertain'] = false;

$configuration['parameters']['docblock'] = [
    'copyrightIdentifier' => 'Copyright (C) ',
    'requiredLicenseIdentifier' => 'GPL-3.0',
];

$configuration['parameters']['ergebnis'] = [
    'noExtends' => [
        'classesAllowedToBeExtended' => [
            Exception::class,
            \TYPO3\CMS\Core\Log\Writer\AbstractWriter::class,
            mteu\StreamWriter\Log\Exception\Exception::class,
        ],
    ],
];

$ignoreErrors = [];

if (count($configuration['parameters']['ignoreErrors']) > 0) {
    foreach ($configuration['parameters']['ignoreErrors'] as $baselineIgnore) {
        $ignoreErrors[] = $baselineIgnore['message'];
    }
}

$configuration['parameters']['ignoreErrors'] = array_unique($ignoreErrors);

return $configuration;
