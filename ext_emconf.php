<?php

/*
 * This file is part of the TYPO3 CMS extension "mteu/typo3-stream-writer".
 *
 * Copyright (C) 2024 Martin Adler <mteu@mailbox.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
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

/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Stream Writer',
    'description' => 'This extension adds the possibility to log to `stdout` and `stderr`.',
    'category' => 'be',
    'version' => '0.1.0',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'author' => 'Martin Adler',
    'author_email' => 'mteu@mailbox.org',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.2.99',
            'php' => '8.1.0-8.3.99',
        ],
    ],
];