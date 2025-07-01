# LogWriter Configuration

## Options
The TYPO3 Stream Writer currently supports following options:

| Option             | Required | Value                                                                                                                                 | Description                                                                                                                              |
|--------------------|----------|---------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------|
| `outputStream`     | required | [`StandardStream::Err`](../Classes/Config/StandardStream.php) or <br/>[`StandardStream::Out`](../Classes/Config/StandardStream.php) | Direct your stream output either to `php://stdout` or `php://stderr`                                                                      |
| `ignoredComponents` | optional | `class-string[]`                                                            | Array of classes to be excluded from showing up through this LogWriter.                                                                  |
| `maxLevel`         | optional | `Psr\Log\LogLevel`                                                                                                                    | Although, you may go with their respective `string` representations, it is recommended to stick to `Psr\Log\LogLevel` for compatibility. |
|                    |          |                                                                                                                                       |                                                                                                                                          |

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
