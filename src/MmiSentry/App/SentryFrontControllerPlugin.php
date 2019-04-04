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
     * @var SentryConfig
     */
    private $_config;

    public function __construct()
    {
        //outside app context
        class_exists('\App\Registry') && isset(\App\Registry::$config) or die('Sentry plugin should be run in MMi application context');
        //configuration missing
        isset(\App\Registry::$config->sentry) or die('Missing sentry configuration');
        //configuration class invalid
        (\App\Registry::$config->sentry instanceof \MmiSentry\Config\SentryConfig) or die('Sentry configuration invalid, must be \MmiSentry\Config\SentryConfig');
        //getting configuration from the registry
        $this->_config = \App\Registry::$config->sentry;
    }

    /**
     * Przed uruchomieniem dispatchera
     * @param \Mmi\Http\Request $request
     */
    public function preDispatch(\Mmi\Http\Request $request)
    {
        //reporting not enabled
        if (!$this->_config->enabled) {
            return;
        }
        //sentry initialization
        \Sentry\init([
            'dsn' => $this->_config->dsn,
        ]);
        //adding user content
        $this->_addUserContext();
    }

    private function _addUserContext()
    {
        //app has no auth object
        if (!isset(\App\Registry::$auth)) {
            return;
        }
        //anonymous
        if (!\App\Registry::$auth->hasIdentity()) {
            return;
        }
        \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
            $scope->setUser([
                'id' => \App\Registry::$auth->getId(),
                'email' => \App\Registry::$auth->getEmail(),
                'username' => \App\Registry::$auth->getUsername(),
            ]);
        });
        return $this;
    }
}
