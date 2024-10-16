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

use mteu\StreamWriter as Src;
use PHPUnit\Framework;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Error\ProductionExceptionHandler;
use TYPO3\CMS\Core\Log\LogRecord;

/**
 * MessageFactoryTest.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\Message\MessageFactory::class)]
final class MessageFactoryTest extends Framework\TestCase
{
    private Src\Log\Message\MessageFactory $subject;

    protected function setUp(): void
    {
        $this->subject = new Src\Log\Message\MessageFactory();
    }

    #[Framework\Attributes\Test]
    public function messageFactoryCreatesLogMessage(): void
    {
        $logRecord = new LogRecord(
            'Foo/Bar/',
            LogLevel::WARNING,
            'FooBarWarningMessage',
        );

        self::assertInstanceOf(
            LogRecordMessage::class,
            $this->subject::createFromRecord($logRecord),
        );
    }

    #[Framework\Attributes\Test]
    public function messageFactoryCreatesExceptionHandlerMessage(): void
    {
        $logRecord = new LogRecord(
            ProductionExceptionHandler::class,
            LogLevel::WARNING,
            'FooBarWarningMessage',
        );

        self::assertInstanceOf(
            ExceptionHandlerLogRecordMessage::class,
            $this->subject::createFromRecord($logRecord),
        );
    }
}
