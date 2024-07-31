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
use PHPUnit\Framework;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;

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
     * @param array{outputStream: mixed}|null $options
     * @throws InvalidLogWriterConfigurationException
     * @phpstan-ignore-next-line
     */
    private function createWriter(array $options = null): Src\Log\Writer\StandardStreamWriter
    {
        if ($options === null) {
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
        WriterInterface $writer,
        LogRecord $record,
    ): string|false {
        ob_start();
        $writer->writeLog($record);

        return ob_get_clean();
    }

    #[Test]
    public function writeLogCreationSucceedsWithEmptyConfiguration(): void
    {
        $subject = $this->createWriter();
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        self::assertInstanceOf(Src\Log\Writer\StandardStreamWriter::class, $subject);
    }

    #[Test]
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

    #[Test]
    public function writeLogCreationThrowsExceptionForInvalidConfiguration(): void
    {
        $this->expectException(\TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException::class);
        /** @phpstan-ignore argument.type */
        $this->createWriter([]);
    }

    #[Test]
    public function writeLogCreationThrowsExceptionForInvalidOutputStreamValue(): void
    {
        $this->expectException(\TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException::class);
        $this->createWriter(['outputStream' => null]);
    }

    //    #[Test]
    //    public function writeLogSucceedsInWritingErrorsToStdErr(): void
    //    {
    //        $output = $this->captureOutputBufferForLogWrite(
    //            $this->createWriter(),
    //            new LogRecord(
    //                'Foo',
    //                LogLevel::ERROR,
    //                'Bar',
    //            ),
    //        );
    //
    //        var_dump($output);
    //        die();
    //
    //        self::assertEquals(
    //            '[Error] - Foo: Bar',
    //            $output
    //        );
    //    }
}