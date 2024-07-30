# TYPO3 Stream Writer
Add logging capabilities to `stdout` and `stderr`.

```php
# ext_localconf.php
# ...

'writerConfiguration' => [
    [
        Mteu\StreamWriter\Log\Writer\StandardStreamWriter::class => [
            'stream' => Mteu\StreamWriter\Log\Config\StandardStream::StdErr,
        ],
    ],
],

# ...
```
