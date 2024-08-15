<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extensions "mteu/typo3-stream-writer".
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

namespace mteu\StreamWriter\Log\Writer;

use mteu\StreamWriter\Log\Config\StandardStream;
use TYPO3\CMS\Core\Log\Exception\InvalidLogWriterConfigurationException;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;

/**
 * StreamWriter.
 *
 * @author Martin Adler <mteu@mailbox.org>
 * @license GPL-3.0-or-later
 */
final class StandardStreamWriter extends AbstractWriter
{
    private readonly StandardStream $outputStream;

    /**
     * @param array{outputStream: StandardStream} $options
     * @throws InvalidLogWriterConfigurationException
     */
    public function __construct(array $options = ['outputStream' => StandardStream::Error])
    {
        parent::__construct();

        $this->outputStream = $this->validateWriterOptions($options);
    }

    /**
     * @param mixed[] $options
     * @throws InvalidLogWriterConfigurationException
     */
    private function validateWriterOptions(array $options): StandardStream
    {
        if (!array_key_exists('outputStream', $options) ||
            $options['outputStream'] === '' ||
            $options['outputStream'] === null
        ) {
            throw new InvalidLogWriterConfigurationException('Missing LogWriter configuration option "outputStream" for log writer of type "' . __CLASS__ . '"', 1722422118);
        }

        if (!$options['outputStream'] instanceof StandardStream) {
            throw new InvalidLogWriterConfigurationException('Invalid LogWriter configuration option "' . $options['outputStream'] . '" for log writer of type "' . __CLASS__ . '"', 1722422119);
        }

        return $options['outputStream'];
    }

    public function writeLog(LogRecord $record): WriterInterface
    {
        $resource = fopen($this->outputStream->value, 'w');

        if ($resource === false) {
            throw new \RuntimeException('Unable to write to ' . $this->outputStream->value . '.', 1722331957);
        }

        $output = fwrite(
            $resource,
            // why custom format when  LogRecord is stringable
            sprintf(
                '[%s] %s: %s' . PHP_EOL,
                $record->getLevel(),
                $record->getComponent(),
                $record->getMessage(),
            ),
        );

        if ($output === false) {
            throw new \RuntimeException('Unable to write to ' . $this->outputStream->value . '.', 1722331958);
        }

        return $this;
    }
}
