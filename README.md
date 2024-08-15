<div align="center">

[![codecov](https://codecov.io/gh/mteu/typo3-stream-writer/graph/badge.svg?token=XIx5ikuAYF)](https://codecov.io/gh/mteu/typo3-stream-writer)
[![Coverage Status](https://coveralls.io/repos/github/mteu/typo3-stream-writer/badge.svg)](https://coveralls.io/github/mteu/typo3-stream-writer)

# TYPO3 Stream Writer

</div>
This TYPO3 CMS extensions adds a custom `LogWriter` to the TYPO3 Logging Framework allowing the CMS to log messages to
`php://stdout` or `php://stderr`.

## ⚡️ Quickstart

### Installation
```bash
composer require mteu/typo3-stream-writer
```

### Usage
Configure your extension or TYPO3 instance to use the new writer.

```php
# config/system/additional.php | typo3conf/system/additional.php

<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogLevel;
use mteu\StreamWriter\Log\Writer\StandardStreamWriter;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
    \Psr\Log\LogLevel::ERROR => [
        StandardStreamWriter::class => [
            'outputStream' => mteu\StreamWriter\Log\Config\StandardStream::Error,
        ],
    ],
    \Psr\Log\LogLevel::WARNING => [
        StandardStreamWriter::class => [
            'outputStream' => mteu\StreamWriter\Log\Config\StandardStream::Out,
        ],
    ],
];
```
## ⭐ License
This project is licensed under [GNU General Public License 3.0 (or later)](LICENSE).
