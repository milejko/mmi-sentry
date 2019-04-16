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

    //class name consts
    const CLASS_NAME_NOT_FOUND_EXCEPTION = 'Mmi\Mvc\MvcNotFoundException';
    const CLASS_NAME_REGISTRY = '\App\Registry';

    //messages consts
    const MESSAGE_REGISTRY_ERROR = 'Sentry plugin should be run in MMi application context';
    const MESSAGE_CONFIG_MISSING = 'Missing Sentry configuration';
    const MESSAGE_CONFIG_INVALID = 'Sentry configuration invalid, must be instance of \MmiSentry\Config\SentryConfig';

    //sentry consts
    const SENTRY_KEY_DSN = 'dsn';
    const SENTRY_KEY_ENVIRONMENT = 'environment';
    const SENTRY_KEY_BEFORE_SEND = 'before_send';
    const SENTRY_KEY_TYPE = 'type';
    const SENTRY_AUTH_ID = 'id';
    const SENTRY_AUTH_EMAIL = 'email';
    const SENTRY_AUTH_USERNAME = 'username';

    /**
     * @var SentryConfig
     */
    private $_config;

    public function __construct()
    {
        //outside app context
        class_exists(self::CLASS_NAME_REGISTRY) && isset(\App\Registry::$config) or die(self::MESSAGE_REGISTRY_ERROR);
        //configuration missing
        isset(\App\Registry::$config->sentry) or die(self::MESSAGE_CONFIG_MISSING);
        //configuration class invalid
        (\App\Registry::$config->sentry instanceof \MmiSentry\Config\SentryConfig) or die(self::MESSAGE_CONFIG_INVALID);
        //getting configuration from the registry
        $this->_config = \App\Registry::$config->sentry;
    }

    /**
     * Przed uruchomieniem dispatchera
     * @param \Mmi\Http\Request $request
     */
    public function routeStartup(\Mmi\Http\Request $request)
    {
        //reporting not enabled
        if (!$this->_config->enabled) {
            return;
        }
        //sentry initialization
        \Sentry\init([
            self::SENTRY_KEY_DSN => $this->_config->dsn,
            //environment - configured, or guessed by config class name
            self::SENTRY_KEY_ENVIRONMENT => $this->_config->environment ? $this->_config->environment : substr(get_class(\App\Registry::$config), 10),
            //before send event
            self::SENTRY_KEY_BEFORE_SEND => function (\Sentry\Event $event) {
                //getting exception list
                $exceptions = $event->getExceptions();
                //ignoring not found exception
                if (isset($exceptions[0][self::SENTRY_KEY_TYPE]) && self::CLASS_NAME_NOT_FOUND_EXCEPTION == $exceptions[0][self::SENTRY_KEY_TYPE]) {
                    return;
                }
                return $event;
            },
        ]);
        //adding user content
        $this->_addUserContext();
    }

    /**
     * Adding user context
     */
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
        //configure scopes
        \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
            //user scope
            $scope->setUser([
                self::SENTRY_AUTH_ID => \App\Registry::$auth->getId(),
                self::SENTRY_AUTH_EMAIL => \App\Registry::$auth->getEmail(),
                self::SENTRY_AUTH_USERNAME => \App\Registry::$auth->getUsername(),
            ]);
        });
    }
}
