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

namespace mteu\StreamWriter\Log;

/**
 * @codeCoverageIgnore
 */
enum LogLevel: string
{
    case ALERT     = 'alert';
    case CRITICAL  = 'critical';
    case DEBUG     = 'debug';
    case EMERGENCY = 'emergency';
    case ERROR     = 'error';
    case INFO      = 'info';
    case NOTICE    = 'notice';
    case WARNING   = 'warning';

    public function priority(): int
    {
        return match ($this) {
            self::EMERGENCY => 8,
            self::ALERT => 7,
            self::CRITICAL => 6,
            self::ERROR => 5,
            self::WARNING => 4,
            self::NOTICE => 3,
            self::INFO => 2,
            self::DEBUG => 1,
        };
    }
}
