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

namespace mteu\StreamWriter\Writer;

use mteu\StreamWriter\Config\LogLevel;
use mteu\StreamWriter\Config\StandardStream;
use mteu\StreamWriter\Exception\InvalidLogWriterConfigurationException;
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
     * @return LogLevel|StandardStream|class-string[]
     * @throws InvalidLogWriterConfigurationException
     */
    public function getOption(string $option): array|LogLevel|StandardStream
    {
        return match ($option) {
            'outputStream' => $this->outputStream,
            'maxLevel' => $this->maxLevel,
            'ignoredComponents' => $this->ignoredComponents,
            default => throw new InvalidLogWriterConfigurationException(
                'Option ' . $option . ' does not exist.',
                1726173519
            ),
        };
    }

    /**
     * @param mixed[] $options
     * @return class-string[]
     * @throws InvalidLogWriterConfigurationException
     */
    private function getIgnoredComponentsOption(array $options): array
    {
        $classes = [];

        if (array_key_exists('ignoredComponents', $options)) {

            if (!is_array($options['ignoredComponents'])) {
                $this->throwConfigurationException('Invalid', 'ignoredComponents', 1722422118);
            }

            if ($options['ignoredComponents'] === []) {
                return [];
            }

            foreach ($options['ignoredComponents'] as $component) {

                if (!is_string($component)) {
                    throw new InvalidLogWriterConfigurationException(
                        'Invalid \'ignoredComponents option type\' for log writer of type "' . __CLASS__ . '"',
                        1726170401,
                    );
                }

                // transposes TYPO3's dotted fqcn to actual fqcn
                $component = str_replace('.', '\\', $component);

                // we're silently accepting and ignoring a potential misconfiguration for invalid class references here
                if (class_exists($component)) {
                    $classes[] = $component;
                }
            }
        }

        return $classes;
    }

    private function throwConfigurationException(string $type, string $option, int $code): never
    {
        throw new InvalidLogWriterConfigurationException(
            $type . ' LogWriter configuration option "' . $option . '" for log writer of type "' . __CLASS__ . '"',
            $code,
        );
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
            $this->throwConfigurationException('Missing', 'outputStream', 1722422117);
        }

        if (!$options['outputStream'] instanceof StandardStream) {
            $this->throwConfigurationException('Invalid', 'outputStream', 1722422116);
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
