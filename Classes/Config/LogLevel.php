<?php

declare(strict_types=1);

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

namespace mteu\StreamWriter\Config;

/**
 * @codeCoverageIgnore
 */
enum LogLevel: string
{
    case Alert     = 'alert';
    case Critical  = 'critical';
    case Debug     = 'debug';
    case Emergency = 'emergency';
    case Error     = 'error';
    case Info      = 'info';
    case Notice    = 'notice';
    case Warning   = 'warning';

    public function priority(): int
    {
        return match ($this) {
            self::Emergency => 8,
            self::Alert => 7,
            self::Critical => 6,
            self::Error => 5,
            self::Warning => 4,
            self::Notice => 3,
            self::Info => 2,
            self::Debug => 1,
        };
    }
}
