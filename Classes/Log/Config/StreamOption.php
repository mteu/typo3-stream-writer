<?php

declare(strict_types=1);

namespace Mteu\StreamWriter\Log\Config;


/**
 * StreamOption.
 *
 * @author Martin Adler <martin.adler@init.de>
 * @license GPL-3.0-or-later
 */
enum StreamOption: string
{
    case StdOut = 'php://stdout';
    case StdErr = 'php://stderr';
}
