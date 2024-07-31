<?php

declare(strict_types=1);

$ignoreErrors = [];
$ignoreErrors[] = [
    // identifier: staticMethod.alreadyNarrowedType
    'message' => '#^Call to static method PHPUnit\\\\Framework\\\\Assert\\:\\:assertInstanceOf\\(\\) with \'Mteu\\\\\\\\StreamWriter\\\\\\\\Log\\\\\\\\Writer\\\\\\\\StandardStreamWriter\' and Mteu\\\\StreamWriter\\\\Log\\\\Writer\\\\StandardStreamWriter will always evaluate to true\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/Tests/Unit/Log/Writer/StandardStreamWriterTest.php',
];
$ignoreErrors[] = [
    // identifier: method.unused
    'message' => '#^Method Mteu\\\\StreamWriter\\\\Tests\\\\Unit\\\\Log\\\\Writer\\\\StandardStreamWriterTest\\:\\:captureOutputBufferForLogWrite\\(\\) is unused\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/Tests/Unit/Log/Writer/StandardStreamWriterTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Method Mteu\\\\StreamWriter\\\\Tests\\\\Unit\\\\Log\\\\Writer\\\\StandardStreamWriterTest\\:\\:createWriter\\(\\) has parameter \\$options with null as default value\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/Tests/Unit/Log/Writer/StandardStreamWriterTest.php',
];
$ignoreErrors[] = [
    // identifier: argument.type
    'message' => '#^Parameter \\#1 \\$options of class Mteu\\\\StreamWriter\\\\Log\\\\Writer\\\\StandardStreamWriter constructor expects array\\{outputStream\\: Mteu\\\\StreamWriter\\\\Log\\\\Config\\\\StandardStream\\}, array\\{outputStream\\: mixed\\} given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/Tests/Unit/Log/Writer/StandardStreamWriterTest.php',
];
$ignoreErrors[] = [
    // identifier: argument.type
    'message' => '#^Parameter \\#1 \\$options of method Mteu\\\\StreamWriter\\\\Tests\\\\Unit\\\\Log\\\\Writer\\\\StandardStreamWriterTest\\:\\:createWriter\\(\\) expects array\\{outputStream\\: mixed\\}\\|null, array\\{\\} given\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/Tests/Unit/Log/Writer/StandardStreamWriterTest.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
