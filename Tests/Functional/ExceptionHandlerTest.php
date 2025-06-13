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

namespace mteu\StreamWriter\Tests\Functional;

use mteu\StreamWriter\Config\ExceptionHandlers;
use mteu\StreamWriter\Config\StandardStream;
use mteu\StreamWriter\Writer\StreamWriter;
use PHPUnit\Framework;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Error\ExceptionHandlerInterface;
use TYPO3\CMS\Core\Error\ProductionExceptionHandler;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Frontend\ContentObject\Exception\ExceptionHandlerInterface as FrontendExceptionHandlerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ExceptionHandlerTest.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
final class ExceptionHandlerTest extends UnitTestCase
{

    #[Framework\Attributes\Test]
    public function streamWriterFormatsExceptionLogRecord(): void
    {
        $logRecord = new LogRecord(
            ProductionExceptionHandler::class,
            LogLevel::WARNING,
            'Test exception occurred',
            [
                'mode' => 'BE',
                'application_mode' => 'WEB',
                'exception_class' => 'RuntimeException',
                'exception_code' => 1234,
                'file' => '/test/file.php',
                'line' => 42,
                'message' => 'Test exception message',
            ],
        );

        $message = \mteu\StreamWriter\Log\Message\MessageFactory::createFromRecord($logRecord);
        $formattedOutput = $message->print();
        
        self::assertStringContainsString('[WARNING]', $formattedOutput);
        self::assertStringContainsString('ProductionExceptionHandler', $formattedOutput);
        self::assertStringContainsString('(BE: WEB)', $formattedOutput);
        self::assertStringContainsString('RuntimeException', $formattedOutput);
        self::assertStringContainsString('code 1234', $formattedOutput);
        self::assertStringContainsString('file /test/file.php', $formattedOutput);
        self::assertStringContainsString('line 42', $formattedOutput);
        self::assertStringContainsString('Test exception message', $formattedOutput);
    }

    #[Framework\Attributes\Test]
    public function exceptionHandlerEnumContainsCoreExceptionHandler(): void
    {
        self::assertEquals(
            ExceptionHandlerInterface::class,
            ExceptionHandlers::CoreError->value
        );
    }

    #[Framework\Attributes\Test]
    public function exceptionHandlerEnumContainsFrontendExceptionHandler(): void
    {
        self::assertEquals(
            FrontendExceptionHandlerInterface::class,
            ExceptionHandlers::FrontendContentObject->value
        );
    }
}