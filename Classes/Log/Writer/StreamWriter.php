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
use mteu\StreamWriter\Log\LogLevel;
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
final class StreamWriter extends AbstractWriter
{
    /**
     * @var class-string[]
     */
    private readonly array $ignoredComponents;

    /**
     * @var class-string[] $exceptionHandlers
     */
    private array $exceptionHandlers = [
        \TYPO3\CMS\Core\Error\ExceptionHandlerInterface::class,
        \TYPO3\CMS\Frontend\ContentObject\Exception\ExceptionHandlerInterface::class,
    ];

    private readonly LogLevel $maxLevel;

    private readonly StandardStream $outputStream;

    /**
     * @param array{outputStream: StandardStream} $options
     * @throws InvalidLogWriterConfigurationException
     */
    public function __construct(array $options = ['outputStream' => StandardStream::Error])
    {
        parent::__construct();

        $this->maxLevel = $this->determineMaximalLevel($options);
        $this->outputStream = $this->getOutputStreamOption($options);
        $this->ignoredComponents = $this->getIgnoredComponentsOption($options);
    }

    /**
     * @param mixed[] $options
     * @return class-string[]
     */
    private function getIgnoredComponentsOption(array $options): array
    {
        if (array_key_exists('ignoreComponents', $options)) {
            return $options['ignoreComponents'];
        }

        return [];
    }

    /**
     * @param mixed[] $options
     * @throws InvalidLogWriterConfigurationException
     */
    private function getOutputStreamOption(array $options): StandardStream
    {
        if (!array_key_exists('outputStream', $options) ||
            $options['outputStream'] === '' ||
            $options['outputStream'] === null
        ) {
            throw new InvalidLogWriterConfigurationException(
                'Missing LogWriter configuration option "outputStream" for log writer of type "' . __CLASS__ . '"',
                1722422118,
            );
        }

        if (!$options['outputStream'] instanceof StandardStream) {
            throw new InvalidLogWriterConfigurationException(
                'Invalid LogWriter configuration option "' . $options['outputStream'] . '" for log writer of type "' . __CLASS__ . '"',
                1722422119,
            );
        }

        return $options['outputStream'];
    }

    public function writeLog(LogRecord $record): WriterInterface
    {
        if (in_array($record->getComponent(), $this->ignoredComponents, true)) {
            return $this;
        }

        if (!$this->levelIsWithinBounds($record)) {
            return $this;
        }

        $this->writeToResource($record);

        return $this;
    }

    private function writeToResource(LogRecord $record): void
    {
        $resource = fopen($this->outputStream->value, 'w');

        if ($resource === false) {
            throw new \RuntimeException('Unable to write to ' . $this->outputStream->value . '.', 1722331957);
        }

        $output = fwrite(
            $resource,
            sprintf(
                '[%s] %s: %s' . PHP_EOL,
                strtoupper($record->getLevel()),
                $record->getComponent(),
                $this->isExceptionHandler($record->getComponent()) ?
                    $this->generateMessageForExceptionHandler($record) :
                    $record->getMessage(),
            ),
        );

        if ($output === false || $output === 0) {
            throw new \RuntimeException('Unable to write to ' . $this->outputStream->value . '.', 1722331958);
        }
    }

    private function generateMessageForExceptionHandler(LogRecord $record): string
    {
        /**
         * @var array{
         *     mode: string,
         *     application_mode: string,
         *     exception_class: string,
         *     exception_code: int,
         *     file: string,
         *     line: int,
         *     message: string,
         * } $data
         */
        $data = $record->getData();

        return sprintf(
            '(%s: %s) %s, code %d, file %s, line %d: %s',
            $data['mode'],
            $data['application_mode'],
            $data['exception_class'],
            $data['exception_code'],
            $data['file'],
            $data['line'],
            $data['message'],
        );
    }

    private function isExceptionHandler(string $component): bool
    {
        $classString = str_replace('.', '\\', $component);

        foreach ($this->exceptionHandlers as $handler) {
            if (is_a($classString, $handler, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[] $options
     */
    private function determineMaximalLevel(array $options): LogLevel
    {
        $default = LogLevel::CRITICAL;

        if (!array_key_exists('maxLevel', $options)) {
            return $default;
        }

        return LogLevel::tryFrom($options['maxLevel']) ?? $default;
    }

    private function levelIsWithinBounds(LogRecord $record): bool
    {
        $logLevelPriority = LogLevel::tryFrom($record->getLevel())?->priority();

        return $logLevelPriority <= $this->maxLevel->priority();
    }
}
