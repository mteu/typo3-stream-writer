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

use mteu\StreamWriter as Src;
use PHPUnit\Framework;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Error\ProductionExceptionHandler;
use TYPO3\CMS\Core\Log\LogRecord;

/**
 * ExceptionHandlerMessageTest.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\ValueObjects\ExceptionHandlerLogRecordMessage::class)]
final class ExceptionHandlerLogRecordMessageTest extends Framework\TestCase
{
    private LogRecord $logRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logRecord = new LogRecord(
            ProductionExceptionHandler::class,
            LogLevel::WARNING,
            'Foo',
            [
                'mode' => 'BE',
                'application_mode' => 'WEB',
                'exception_class' => 'Foo/ExceptionClass',
                'exception_code' => 123,
                'file' => '/foo/bar.php',
                'line' => 1,
                'message' => 'Message',
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function printLogRecordMessageMatchesDesiredFormat(): void
    {
        $logRecordMessage = ExceptionHandlerLogRecordMessage::create($this->logRecord);

        self::assertEquals(
            '[WARNING] TYPO3\CMS\Core\Error\ProductionExceptionHandler: (BE: WEB) Foo/ExceptionClass, code 123, file /foo/bar.php, line 1: Message' . PHP_EOL,
            $logRecordMessage->print(),
        );
    }
}
