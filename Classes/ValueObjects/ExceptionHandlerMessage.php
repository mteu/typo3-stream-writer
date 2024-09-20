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


use TYPO3\CMS\Core\Log\LogRecord;

/**
 * LogOutput.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
final readonly class ExceptionHandlerMessage implements Message
{
        public function __construct(
            private string $mode,
            private string $applicationMode,
            private string $exceptionClass,
            private int $exceptionCode,
            private string $file,
            private int $line,
            private string $message,
        )
        {
        }

        public function print(): string
        {
            return sprintf(
                '(%s: %s) %s, code %d, file %s, line %d: %s',
                $this->mode,
                $this->applicationMode,
                $this->exceptionClass,
                $this->exceptionCode,
                $this->file,
                $this->line,
                $this->message,
            );
        }

        /**
         * @param array{
         *     mode: string,
         *     application_mode: string,
         *     exception_class: string,
         *     exception_code: int,
         *     file: string,
         *     line: int,
         *     message: string,
         * } $data
         */
        public static function fromArray(array $data): self
        {
            return new self(
              $data['mode'],
              $data['application_mode'],
              $data['exception_class'],
              (int)$data['exception_code'],
              $data['file'],
              (int)$data['line'],
              $data['message'],
            );
        }

        public static function create(LogRecord $record): self
        {
            return self::fromArray($record->getData());
        }
}
