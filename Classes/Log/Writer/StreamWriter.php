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

namespace Mteu\StreamWriter\Log\Writer;

use Mteu\StreamWriter\Log\Config\StreamOption;
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
    private StreamOption $streamOption;
    /**
     * @param array{stream: StreamOption} $options
     * @throws InvalidLogWriterConfigurationException
     */
    public function __construct(array $options = ['stream' => StreamOption::StdErr])
    {
        parent::__construct($options);
        $this->streamOption = $options['stream'];
    }

    public function writeLog(LogRecord $record): WriterInterface
    {
        $resource = @fopen($this->streamOption->value, 'w');

        if (false === $resource) {
            throw new \RuntimeException('Unable to write to ' .  $this->streamOption->value . '.', 1722331957);
        }

        $output = fputs(
            $resource,
            trim(
                sprintf(
                    '%s: %s',
                    $record->getComponent(),
                    $record->getMessage(),
                ),
            ) . PHP_EOL,
        );

        if (false === $output) {
            throw new \RuntimeException('Unable to write to ' .  $this->streamOption->value . '.', 1722331958);
        }

        return $this;
    }
}
