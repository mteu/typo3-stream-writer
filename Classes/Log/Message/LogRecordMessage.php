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

namespace mteu\StreamWriter\Log\Message;

use TYPO3\CMS\Core\Log\LogRecord;

/**
 * LogMessage.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
final class LogRecordMessage implements Message
{
    public function __construct(
        private readonly string $level,
        private readonly string $component,
        private readonly string $message,
    ) {}

    public static function create(LogRecord $record): self
    {
        return new self(
            $record->getLevel(),
            $record->getComponent(),
            $record->getMessage(),
        );
    }

    public function print(): string
    {
        return sprintf(
            '[%s] %s: %s' . PHP_EOL,
            strtoupper($this->level),
            $this->component,
            $this->message,
        );
    }
}
