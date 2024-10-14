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

namespace mteu\StreamWriter\ValueObjects;

use mteu\StreamWriter\Config\ExceptionHandlers;
use TYPO3\CMS\Core\Log\LogRecord;

/**
 * LogMessage.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
final class LogMessage implements Message
{
        public function __construct(protected readonly LogRecord $record) {}

        public function print(): string
        {
            return sprintf(
                $this->getFormat(),
                strtoupper($this->record->getLevel()),
                $this->record->getComponent(),
                $this->generateMessage(),
            );
        }

        private function getFormat(): string
        {
            return '[%s] %s: %s' . PHP_EOL;
        }

        private function generateMessage(): string
        {
            return $this->isExceptionHandler($this->record->getComponent()) ?
                $this->generateMessageForExceptionHandler($this->record) :
                $this->record->getMessage();
        }

        private function isExceptionHandler(string $component): bool
        {
            $classString = str_replace('.', '\\', $component);

            foreach (ExceptionHandlers::cases() as $handler) {
                if (is_a($classString, $handler->value, true)) {
                    return true;
                }
            }

            return false;
        }

        private function generateMessageForExceptionHandler(LogRecord $record): string
        {
            return ExceptionHandlerMessage::create($record)->print();
        }
}
