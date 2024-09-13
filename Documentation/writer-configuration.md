# LogWriter Configuration

## Options
The TYPO3 Stream Writer currently supports following options:

| Option             | Required | Value                                                                                                                                       | Description                                                                                                                                                                                            |
|--------------------|----------|---------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `outputStream`     | required | [`StandardStream::Err`](../Classes/Log/Config/StandardStream.php) or <br/>[`StandardStream::Out`](../Classes/Log/Config/StandardStream.php) | Direct your stream output either to `php://stdout` or `php://sterr`                                                                                                                                    |
| `ignoredComponents` | optional | Array of classes to be excluded from logging via this LogWriter                                                                             | Please note that classes are currently repesented by the full qualified class name syntax the TYPO3 Logging Framework uses.<br/> _This is likely to change to accept actual class strings there, too_. |
| `maxLevel`         | optional | `Psr\Log\LogLevel`                                                                                                                          | Although, you may go with their respective `string` representations, it is recommended to stick to `Psr\Log\LogLevel` for compatibility.                                                               |
|                    |          |                                                                                                                                             |                                                                                                                                                                                                        |

## Example

```php
$GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'] = [
    \Psr\Log\LogLevel::DEBUG => [
        StreamWriter::class => [
            'outputStream' => StandardStream::Out,
            'ignoredComponents' => [
                BackendUserAuthentication::class,
                'TYPO3.CMS.Frontend.Authentication.FrontendUserAuthentication',
            ],
            'maxLevel' => Psr\Log\LogLevel::WARNING,
        ],
    ],
];
```
