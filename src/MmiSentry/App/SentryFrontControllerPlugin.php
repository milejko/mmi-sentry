<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 * 
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2019 Mariusz Miłejko (http://milejko.com)
 * @license    http://milejko.com/new-bsd.txt New BSD License
 */

namespace MmiSentry\App;

/**
 * Mmi Sentry front controller plugin
 */
class SentryFrontControllerPlugin extends \Mmi\App\FrontControllerPluginAbstract
{

    /**
     * Przed uruchomieniem dispatchera
     * @param \Mmi\Http\Request $request
     */
    public function preDispatch(\Mmi\Http\Request $request)
    {
        echo 'PRED';
    }
}
