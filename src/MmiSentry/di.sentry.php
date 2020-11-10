<?php

use Psr\Container\ContainerInterface;
use Sentry\Event;
use Sentry\State\Scope;

use function DI\env;
use function Sentry\configureScope;
use function Sentry\init;

return [
    'sentry.dsn'                => env('SENTRY_DSN', ''),
    'sentry.environment'        => env('SENTRY_ENVIRONMENT', 'LOCAL'),
    'sentry.ignore.exception'   => env('SENTRY_IGNORE.EXCEPTION', 'Mmi\Mvc\MvcNotFoundException'),
    'sentry.enabled'            => env('SENTRY_ENABLED', 0),

    'sentry.service' => function (ContainerInterface $container): bool {
        //sentry disabled
        if (!$container->get('sentry.enabled')) {
            return false;
        }
        //defining ignored exceptions
        define('SENTRY_IGNORED_EXCEPTIONS', explode(',', $container->get('sentry.ignore.exception')));
        //sentry initialization
        init([
            'dsn' => $container->get('sentry.dsn'),
            //environment - configured, or guessed by config class name
            'environment' => $container->get('sentry.environment'),
            //before send event
            'before_send' => function (Event $event) {
                //iterate exceptions
                foreach ($event->getExceptions() as $exception) {
                    //exception type on an ignored list
                    if (in_array($exception['type'], SENTRY_IGNORED_EXCEPTIONS)) {
                        return;
                    }
                }
                return $event;
            },
        ]);
        //user scope
        if (
            isset($_SESSION['Auth']) && 
            is_array($_SESSION['Auth']) && 
            isset($_SESSION['Auth']['id']) &&
            isset($_SESSION['Auth']['username']) &&
            isset($_SESSION['Auth']['email'])
        ) {
            configureScope(function (Scope $scope): void {
                //user scope
                $scope->setUser([
                    'id'            => $_SESSION['Auth']['id'],
                    'email'         => $_SESSION['Auth']['email'],
                    'username'      => $_SESSION['Auth']['username'],
                    'ip_address'    => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                ], true);
            });
        }
        return true;
    },
];