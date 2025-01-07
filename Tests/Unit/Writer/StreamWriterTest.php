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

namespace mteu\StreamWriter\Tests\Unit\Writer;

use mteu\StreamWriter as Src;
use PHPUnit\Framework;
use Psr\Log\LogLevel;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Error\ProductionExceptionHandler;
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
        foreach (Src\Config\StandardStream::cases() as $standardStream) {
            /** @phpstan-ignore staticMethod.alreadyNarrowedType */
            self::assertInstanceOf(
                Src\Writer\StreamWriter::class,
                $this->createWriter(['outputStream' => $standardStream])
            );
        }
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideLogRecordsForStandardStream')]
    public function writeLogRespectsMaximumLevelBoundary(
        Src\Config\StandardStream $stream,
        LogRecord $record,
        string $expected,
    ): void {

        foreach (Src\Config\LogLevel::cases() as $potentialMaxLevel) {
            $recordLevelPriority = Src\Config\LogLevel::from($record->getLevel())->priority();

            match (true) {
                // level is outside bounds
                $recordLevelPriority > $potentialMaxLevel->priority() => self::assertEmpty(
                    $this->writeToStreamInSeparateProcess(
                        $stream,
                        $record,
                        $potentialMaxLevel,
                        __METHOD__,
                    ),
                ),
                // level is within bounds or null
                default => self::assertSame(
                    $expected,
                    $this->writeToStreamInSeparateProcess(
                        $stream,
                        $record,
                        $potentialMaxLevel,
                        __METHOD__,
                    ),
                ),
            };
        }
    }

    /**
     * @param string $className Can be both string|class-string
     * @throws Src\Exception\InvalidLogWriterConfigurationException
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideClassNames')]
    public function writeLogThrowsExceptionForInvalidTypesInIgnoringSpecifiedComponents(
        string $className,
    ): void {
        $this->expectExceptionCode(1726170401);
        $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Out,
            'ignoredComponents' => [
                $className,
                '',
                null,
                0,
                new class () {},
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideClassNames')]
    public function writeLogCreationUnderstandsDifferentClassNameNotations(
        string $className,
    ): void {
        $ignorableComponents = [
            $className,
        ];

        $writer = $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Out,
            'ignoredComponents' => $ignorableComponents,
        ]);

        $writerOption = $writer->getOption('ignoredComponents');

        // @todo: improve this
        if ($className === 'TYPO3.CMS.Core.Authentication.BackendUserAuthentication') {
            self::assertNotEquals($ignorableComponents, $writerOption);
        } else {
            self::assertSame($ignorableComponents, $writerOption);
        }

    }

    #[Framework\Attributes\Test]
    public function writeLogOptionsAreCorrectlySetToTheWriter(
    ): void {
        $writer = $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Out,
            'ignoredComponents' => [],
        ]);
        self::assertEquals(Src\Config\StandardStream::Out, $writer->getOption('outputStream'));
        self::assertEquals([], $writer->getOption('ignoredComponents'));

        $writer = $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Error,
            'maxLevel' => LogLevel::ERROR,
        ]);
        self::assertEquals(Src\Config\LogLevel::Error, $writer->getOption('maxLevel'));

        // @todo: move to separate test
        $writer = $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Error,
            'maxLevel' => 'Error',
        ]);
        self::assertEquals(Src\Config\StandardStream::Error, $writer->getOption('outputStream'));
        self::assertEquals(Src\Config\LogLevel::highest(), $writer->getOption('maxLevel'));
    }

    /**
     * @param class-string $className
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideClassNames')]
    public function writeLogCreationSucceedsInIgnoringSpecifiedComponents(
        string $className,
    ): void {
        self::assertEmpty(
            $this->writeToStreamInSeparateProcess(
                Src\Config\StandardStream::Out,
                new LogRecord(
                    BackendUserAuthentication::class,
                    Src\Config\LogLevel::Error->value,
                    'Foo',
                ),
                Src\Config\LogLevel::Emergency,
                __METHOD__,
                $className,
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForInvalidConfiguration(): void
    {
        self::expectException(Src\Exception\InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Invalid LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Writer\StreamWriter');
        $this->createWriter(['outputStream' => 'foo']);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForUnsetOutputStreamValue(): void
    {
        self::expectException(Src\Exception\InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Writer\StreamWriter"');
        $this->createWriter(['outputStream' => null]);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForEmptyValues(): void
    {
        self::expectException(Src\Exception\InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "outputStream" for log writer of type "mteu\StreamWriter\Writer\StreamWriter"');
        $this->createWriter(['outputStream' => '']);

        self::expectException(Src\Exception\InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage('Missing LogWriter configuration option "ignoredComponents" for log writer of type "mteu\StreamWriter\Writer\StreamWriter"');
        $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Out,
            'ignoredComponents' => '',
        ]);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionForUnspecifiedIgnoredComponentsValue(): void
    {
        self::expectException(Src\Exception\InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage(
            'Invalid LogWriter configuration option "ignoredComponents" for log writer of type "mteu\StreamWriter\Writer\StreamWriter"'
        );
        $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Out,
            'ignoredComponents' => null,
        ]);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationThrowsExceptionUnsupportedMaxLevelType(): void
    {
        self::expectException(Src\Exception\InvalidLogWriterConfigurationException::class);
        self::expectExceptionMessage(
            'LogWriter configuration of "maxLevel" must be int|string.'
        );
        $this->createWriter([
            'outputStream' => Src\Config\StandardStream::Out,
            'maxLevel' => [LogLevel::ERROR],
        ]);
    }

    #[Framework\Attributes\Test]
    public function writeLogCreationExtractsStackDataFromExceptionHandler(): void
    {
        $data = [
            'mode' => 'BE',
            'application_mode' => 'WEB',
            'exception_class' => 'Foo/ExceptionClass',
            'exception_code' => 123,
            'file' => '/foo/bar.php',
            'line' => 1,
            'message' => 'Message',
        ];

        $record = new LogRecord(
            ProductionExceptionHandler::class,
            Src\Config\LogLevel::Error->value,
            'Foo',
            $data,
            'Bar',
        );

        $expected = sprintf(
            '[%s] %s: (%s: %s) %s, code %d, file %s, line %d: %s',
            strtoupper($record->getLevel()),
            $record->getComponent(),
            $data['mode'],
            $data['application_mode'],
            $data['exception_class'],
            $data['exception_code'],
            $data['file'],
            $data['line'],
            $data['message'],
        );

        self::assertSame(
            $expected,
            $this->writeToStreamInSeparateProcess(
                Src\Config\StandardStream::Out,
                $record,
                Src\Config\LogLevel::Emergency,
                __METHOD__,
            ),
        );

    }

    private function writeToStreamInSeparateProcess(
        Src\Config\StandardStream $stream,
        LogRecord $record,
        Src\Config\LogLevel $maxLevel,
        string $trigger,
        string $ignoredComponent = '',
    ): string {
        $tempOutputFile = tempnam(sys_get_temp_dir(), 'stream_writer_test_script_');

        file_put_contents(
            $tempOutputFile,
            $this->generatePhpScriptForLogWriting(
                $stream,
                $record,
                $maxLevel,
                $trigger,
                $ignoredComponent,
            ),
        );

        $process = new Process([PHP_BINARY, $tempOutputFile]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $stream === Src\Config\StandardStream::Out ? trim($process->getOutput()) : trim($process->getErrorOutput());

        unlink($tempOutputFile);

        return $output;
    }

    private function generatePhpScriptForLogWriting(
        Src\Config\StandardStream $stream,
        LogRecord $record,
        Src\Config\LogLevel $maxLevel,
        string $trigger,
        string $ignoredComponent = '',
    ): string {
        $autoload = dirname(__DIR__, 3) . '/.build/vendor/autoload.php';
        $classFileName = dirname(__DIR__, 3) . '/Classes/Writer/StreamWriter.php';
        $coverageFile =  dirname(__DIR__, 3) . '/.build/coverage/sub-process_' . uniqid() . '.cov';

        // data will most likely be set for ExceptionHandlers only
        $data = [
            'mode' => $record->getData()['mode'] ?? '',
            'application_mode' => $record->getData()['application_mode'] ?? '',
            'exception_class' => $record->getData()['exception_class'] ?? '',
            'exception_code' => $record->getData()['exception_code'] ?? 0,
            'file' => $record->getData()['file'] ?? '',
            'line' => $record->getData()['line'] ?? 0,
            'message' => $record->getData()['message'] ?? '',
        ];

        return <<<PHP
            <?php

            require_once '$autoload';

            use mteu\StreamWriter\Config\StandardStream;
            use mteu\StreamWriter\Writer\StreamWriter;
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

            \$coverage->start('{$trigger}');


            \$logWriter = new StreamWriter(
                [
                    'outputStream' => StandardStream::from('{$stream->value}'),
                    'maxLevel' => '{$maxLevel->value}',
                    'ignoredComponents' => [
                        '{$ignoredComponent}'
                    ],
                ],
            );
            \$logWriter->writeLog(
                new LogRecord(
                    '{$record->getComponent()}',
                    '{$record->getLevel()}',
                    '{$record->getMessage()}',
                    [
                        'mode' => '{$data['mode']}',
                        'application_mode' => '{$data['application_mode']}',
                        'exception_class' => '{$data['exception_class']}',
                        'exception_code' => '{$data['exception_code']}',
                        'file' => '{$data['file']}',
                        'line' => '{$data['line']}',
                        'message' => '{$data['message']}',
                    ],
                    '{$record->getRequestId()}',
                ),
            );

            \$coverage->stop();
            (new PhpReport)->process(\$coverage, '$coverageFile');
        PHP;
    }

    /**
     * @param mixed[] $options
     * @return Src\Writer\StreamWriter
     * @throws Src\Exception\InvalidLogWriterConfigurationException
     */
    private function createWriter(array $options = []): Src\Writer\StreamWriter
    {
        if ($options === []) {
            return new Src\Writer\StreamWriter();
        }

        /** @phpstan-ignore argument.type */
        return new Src\Writer\StreamWriter($options);
    }

    /**
     * @return \Generator<string, array{Src\Config\StandardStream, LogRecord, string}>
     */
    public static function provideLogRecordsForStandardStream(): \Generator
    {
        foreach (Src\Config\StandardStream::cases() as $stream) {
            $streamKey = strtolower($stream->name);

            foreach (Src\Config\LogLevel::cases() as $logLevel) {
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

    /**
     * @return \Generator<string, string[]>
     */
    public static function provideClassNames(): \Generator
    {
        yield 'constant' => [BackendUserAuthentication::class];
        yield 'fqcn' => [\TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class];
        yield 'simplySlashedClassName' => [\TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class];
        yield 'fullyDottedClassName' => ['TYPO3.CMS.Core.Authentication.BackendUserAuthentication'];
    }
}
