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

use mteu\StreamWriter\Log as Src;
use mteu\StreamWriter\Log\Config\StandardStream;
use mteu\StreamWriter\Log\Writer\StreamWriter;
use PHPUnit\Framework;
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
#[Framework\Attributes\CoversClass(Src\Writer\StreamWriter::class)]
final class StreamWriterTest extends Framework\TestCase
{
    /**
     * @throws \Exception
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('generateLogRecordsForStandardStream')]
    public function writeLogRespectsMaximumLevelBoundary(
        StandardStream $stream,
        LogRecord $record,
        string $expected,
    ): void {

        foreach (Src\LogLevel::cases() as $potentialMaxLevel) {

            $recordLevelPriority = \mteu\StreamWriter\Log\LogLevel::tryFrom($record->getLevel())?->priority();

            match (true) {
                // level is outside bounds
                $recordLevelPriority > $potentialMaxLevel->priority() => self::assertEmpty(
                    $this->writeToStreamInSeparateProcess(
                        $stream,
                        $record,
                        $potentialMaxLevel,
                    ),
                ),
                // level is within bounds or null
                default => self::assertSame(
                    $expected,
                    $this->writeToStreamInSeparateProcess(
                        $stream,
                        $record,
                        $potentialMaxLevel,
                    ),
                ),
            };
        }
    }

    private function writeToStreamInSeparateProcess(
        StandardStream $stream,
        LogRecord $record,
        Src\LogLevel $maxLevel = Src\LogLevel::EMERGENCY,
    ): string {
        $tempOutputFile = tempnam(sys_get_temp_dir(), 'stream_writer_test_script_');

        file_put_contents($tempOutputFile, $this->generatePhpScriptForLogWriting($stream, $record, $maxLevel));

        // @todo: ensure this path is included in the coverage report
        $process = new Process([PHP_BINARY, $tempOutputFile]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $stream === StandardStream::Out ? trim($process->getOutput()) : trim($process->getErrorOutput());

        unlink($tempOutputFile);

        return $output;
    }

    private function generatePhpScriptForLogWriting(
        StandardStream $stream,
        LogRecord $record,
        Src\LogLevel $maxLevel = Src\LogLevel::DEBUG,
    ): string {
        $autoload = dirname(__DIR__, 4) . '/.build/vendor/autoload.php';
        $classFileName = dirname(__DIR__, 4) . '/Classes/Log/Writer/StreamWriter.php';
        $coverageFile =  dirname(__DIR__, 4) . '/.build/coverage/subprocess_' . uniqid() . '.cov';

        return <<<PHP
            <?php

            require_once '$autoload';

            use mteu\StreamWriter\Log\Config\StandardStream;
            use mteu\StreamWriter\Log\Writer\StreamWriter;
            use SebastianBergmann\CodeCoverage\Filter;
            use SebastianBergmann\CodeCoverage\Driver\Selector;
            use SebastianBergmann\CodeCoverage\CodeCoverage;
            use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
            use TYPO3\CMS\Core\Log\LogRecord;

            \$filter = new Filter;
            \$filter->includeFiles(['$classFileName']);

            \$coverage = new CodeCoverage(
                (new Selector)->forLineCoverage(\$filter),
                \$filter
            );

            \$coverage->start('write-log_test');


            \$logWriter = new StreamWriter(
                [
                    'outputStream' => StandardStream::from('{$stream->value}'),
                    'maxLevel' => '{$maxLevel->value}',
                ],
            );
            \$logWriter->writeLog(
                new LogRecord(
                    '{$record->getComponent()}',
                    '{$record->getLevel()}',
                    '{$record->getMessage()}',
                    [],
                    '{$record->getRequestId()}',
                ),
            );

            \$coverage->stop();
            (new PhpReport)->process(\$coverage, '$coverageFile');
        PHP;
    }

    /**
     * @param mixed[] $options
     * @return StreamWriter
     * @throws InvalidLogWriterConfigurationException
     */
    private function createWriter(array $options = []): Src\Writer\StreamWriter
    {
        if ($options === []) {
            return new Src\Writer\StreamWriter();
        }

        /** @phpstan-ignore argument.type */
        return new Src\Writer\StreamWriter($options);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationSucceedsWithEmptyConfiguration(): void
    {
        $subject = $this->createWriter();
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(Src\Writer\StreamWriter::class, $subject);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationSucceedsWithProperlyConfiguredOutputStream(): void
    {
        foreach (StandardStream::cases() as $standardStream) {
            /** @phpstan-ignore staticMethod.alreadyNarrowedType */
            self::assertInstanceOf(
                Src\Writer\StreamWriter::class,
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
    public static function generateLogRecordsForStandardStream(): \Generator
    {
        foreach (StandardStream::cases() as $stream) {
            $streamKey = strtolower($stream->name);

            foreach (Src\LogLevel::cases() as $logLevel) {
                yield 'Write ' . $logLevel->value . ' to ' . $streamKey => [
                    $stream,
                    new LogRecord(
                        $logLevel->value . 'Component',
                        $logLevel->value,
                        $logLevel->value . 'Message',
                        [],
                        'requestId_' . $streamKey,
                    ),
                    sprintf(
                        '[%s] %s: %s',
                        strtoupper($logLevel->value),
                        $logLevel->value . 'Component',
                        $logLevel->value . 'Message',
                    ),
                ];
            }
        }
    }
}
