# TYPO3 Stream Writer
Add logging capabilities to `stdout` and `stderr`.

```php
# ext_localconf.php

<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\LogLevel;
use Mteu\StreamWriter\Log\Writer\StandardStreamWriter;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['LOG']['MyVendor']['MyExtension']['MyClass']['writerConfiguration'] = [
    // Configuration for ERROR level log entries
    LogLevel::ERROR => [
        StandardStreamWriter::class => [
            'stdStream' => Mteu\StreamWriter\Log\Config\StandardStream::Error,
        ],
    ],
];

# ...
```
