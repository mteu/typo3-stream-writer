<?php

declare(strict_types=1);

use Mteu\Typo3StreamWriter\Error\CustomExceptionHandler;

defined('TYPO3') or die();

// Register custom exception handler
$GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler'] = CustomExceptionHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler'] = CustomExceptionHandler::class;
$GLOBALS['TYPO3_CONF_VARS']['SYS']['exceptionalErrors'] = E_ALL & ~(E_STRICT | E_NOTICE);