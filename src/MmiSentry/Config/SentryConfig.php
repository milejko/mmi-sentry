<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2019 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace MmiSentry\Config;

/**
 * Sentry configuration class
 */
class SentryConfig
{

    /**
     * DSN
     * @var string
     */
    public $dsn;

    /**
     * Reporting enabled
     * @var boolean
     */
    public $enabled = true;

    /**
     * Nazwa środowiska
     * @var string
     */
    public $environment;
}
