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
use mteu\StreamWriter\Log\Writer\StreamWriter;
use PHPUnit\Framework;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException;
use TYPO3\CMS\Core\Log\LogRecord;

/**
 * StreamWriterTest.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Log\Writer\StreamWriter::class)]
final class StreamWriterTest extends Framework\TestCase
{
    /**
     * @throws \Exception
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('writeLogProvokesMatchingStreamOutputForLogLevels')]
    public function writeLogSucceedsInWritingToStream(
        StandardStream $stream,
        LogRecord $record,
        string $expected,
    ): void {
        $tempFile = tempnam(sys_get_temp_dir(), 'stream_writer_test_script_');

        file_put_contents($tempFile, $this->generatePhpScriptForLogWriting($stream, $record));

        $process = new Process([PHP_BINARY, $tempFile]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        self::assertSame(
            $expected,
            $stream === StandardStream::Out ? trim($process->getOutput()) : trim($process->getErrorOutput()),
        );

        unlink($tempFile);
    }

    private function generatePhpScriptForLogWriting(StandardStream $stream, LogRecord $record): string
    {
        $autoload = dirname(__DIR__, 4) . '/.build/vendor/autoload.php';
        return <<<PHP
            <?php

            require_once '$autoload';

            use mteu\StreamWriter\Log\Config\StandardStream;
            use mteu\StreamWriter\Log\Writer\StreamWriter;
            use TYPO3\CMS\Core\Log\LogRecord;

            \$logWriter = new StreamWriter(['outputStream' => StandardStream::from('{$stream->value}')]);
            \$logWriter->writeLog(
                new LogRecord(
                    '{$record->getComponent()}',
                    '{$record->getLevel()}',
                    '{$record->getMessage()}',
                ),
            );
        PHP;
    }

    /**
     * @param mixed[] $options
     * @return StreamWriter
     * @throws InvalidLogWriterConfigurationException
     */
    private function createWriter(array $options = []): Src\Log\Writer\StreamWriter
    {
        if ($options === []) {
            return new Src\Log\Writer\StreamWriter();
        }

        /** @phpstan-ignore argument.type */
        return new Src\Log\Writer\StreamWriter($options);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationSucceedsWithEmptyConfiguration(): void
    {
        $subject = $this->createWriter();
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(Src\Log\Writer\StreamWriter::class, $subject);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationSucceedsWithProperlyConfiguredOutputStream(): void
    {
        foreach (StandardStream::cases() as $standardStream) {
            /** @phpstan-ignore staticMethod.alreadyNarrowedType */
            self::assertInstanceOf(
                Src\Log\Writer\StreamWriter::class,
                $this->createWriter(['outputStream' => $standardStream])
            );
        }
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForInvalidConfiguration(): void
    {
        self::expectException(InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Log\Writer\StreamWriter');
        $this->createWriter(['foo']);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForUnsetOutputStreamValue(): void
    {
        self::expectException(InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Log\Writer\StreamWriter"');
        $this->createWriter(['outputStream' => null]);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForEmptyOutputStreamValue(): void
    {
        self::expectException(InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Log\Writer\StreamWriter"');
        $this->createWriter(['outputStream' => '']);
    }

    /**
     * @return \Generator<string, array{StandardStream, LogRecord, string}>
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
            '[CRITICAL] CriticalComponent: CriticalMessage',
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
                'WarningComponent',
                LogLevel::WARNING,
                'WarningMessage',
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
