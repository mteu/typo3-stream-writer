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

namespace mteu\StreamWriter\Tests\Unit\Log\Writer;

use mteu\StreamWriter as Src;
use mteu\StreamWriter\Log\Config\StandardStream;
use mteu\StreamWriter\Log\Writer\StandardStreamWriter;
use PHPUnit\Framework;
use Psr\Log\LogLevel;
use TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException;
use TYPO3\CMS\Core\Log\LogRecord;

/**
 * StandardStreamWriterTest.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\Writer\StandardStreamWriter::class)]
final class StandardStreamWriterTest extends Framework\TestCase
{
    /**
     * @param mixed[] $options
     * @return StandardStreamWriter
     * @throws InvalidLogWriterConfigurationException
     */
    private function createWriter(array $options = []): Src\Log\Writer\StandardStreamWriter
    {
        if ($options === []) {
            return new Src\Log\Writer\StandardStreamWriter();
        }

        /** @phpstan-ignore argument.type */
        return new Src\Log\Writer\StandardStreamWriter($options);
    }

    /**
     * @throws \Exception
     * @phpstan-ignore method.unused
     */
    private function captureOutputBufferForLogWrite(
        StandardStream $stream,
        LogRecord $record,
    ): string|false {

        $logWriter = $this->createWriter(['outputStream' => $stream]);

        ob_start();
        $logWriter->writeLog($record);

        return ob_get_clean();
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationSucceedsWithEmptyConfiguration(): void
    {
        $subject = $this->createWriter();
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(Src\Log\Writer\StandardStreamWriter::class, $subject);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationSucceedsWithProperlyConfiguredOutputStream(): void
    {
        foreach (StandardStream::cases() as $standardStream) {
            /** @phpstan-ignore staticMethod.alreadyNarrowedType */
            self::assertInstanceOf(
                Src\Log\Writer\StandardStreamWriter::class,
                $this->createWriter(['outputStream' => $standardStream])
            );
        }
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForInvalidConfiguration(): void
    {
        self::expectException(InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Log\Writer\StandardStreamWriter');
        $this->createWriter(['foo']);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForUnsetOutputStreamValue(): void
    {
        self::expectException(InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Log\Writer\StandardStreamWriter"');
        $this->createWriter(['outputStream' => null]);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForEmptyOutputStreamValue(): void
    {
        self::expectException(InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Log\Writer\StandardStreamWriter"');
        $this->createWriter(['outputStream' => '']);
    }

    /**
     * @throws \Exception
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('writeLogProvokesMatchingStreamOutputForLogLevels')]
    public function writeLogSucceedsInWritingErrorsToStdErr(
        StandardStream $stream,
        LogRecord $record,
        string $expected,
    ): void {
        $output = $this->captureOutputBufferForLogWrite(
            $stream,
            $record,
        );

        self::assertEquals(
            $expected,
            $output
        );
    }

    /**
     * @return \Generator<string, StandardStream, array{LogRecord, string}>
     */
    public static function writeLogProvokesMatchingStreamOutputForLogLevels(): \Generator
    {
        yield 'emergency' => [
            StandardStream::Error,
            new LogRecord(
                'EmergencyComponent',
                LogLevel::EMERGENCY,
                'EmergencyMessage',
            ),
            '[EMERGENCY] EmergencyComponent: EmergencyMessage',
        ];

        yield 'alert' => [
            StandardStream::Error,
            new LogRecord(
                'AlertComponent',
                LogLevel::ALERT,
                'AlertMessage',
            ),
            '[ALERT] AlertComponent: AlertMessage',
        ];

        yield 'critical' => [
            StandardStream::Error,
            new LogRecord(
                'CriticalComponent',
                LogLevel::CRITICAL,
                'CriticalMessage',
            ),
            '[CRITICAL] AlertComponent: CriticalMessage',
        ];

        yield 'error' => [
            StandardStream::Error,
            new LogRecord(
                'ErrorComponent',
                LogLevel::ERROR,
                'ErrorMessage',
            ),
            '[ERROR] ErrorComponent: ErrorMessage',
        ];

        yield 'warning' => [
            StandardStream::Out,
            new LogRecord(
                'warningComponent',
                LogLevel::WARNING,
                'warningMessage',
            ),
            '[WARNING] WarningComponent: WarningMessage',
        ];

        yield 'notice' => [
            StandardStream::Out,
            new LogRecord(
                'NoticeComponent',
                LogLevel::NOTICE,
                'NoticeMessage',
            ),
            '[NOTICE] NoticeComponent: NoticeMessage',
        ];

        yield 'info' => [
            StandardStream::Out,
            new LogRecord(
                'InfoComponent',
                LogLevel::INFO,
                'InfoMessage',
            ),
            '[INFO] InfoComponent: InfoMessage',
        ];

        yield 'debug' => [
            StandardStream::Out,
            new LogRecord(
                'DebugComponent',
                LogLevel::DEBUG,
                'DebugMessage',
            ),
            '[DEBUG] DebugComponent: DebugMessage',
        ];
    }
}
