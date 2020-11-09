<?php

use Mmi\Security\Auth;
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

    'sentry.loaded'     => function (ContainerInterface $container) {
        //sentry disabled
        if (!$container->get('sentry.enabled')) {
            return false;
        }
        //sentry initialization
        init([
            'dsn' => $container->get('sentry.dsn'),
            //environment - configured, or guessed by config class name
            'environment' => $container->get('sentry.environment'),
            //before send event
            'before_send' => function (Event $event, ContainerInterface $container) {
                //iterate exceptions
                foreach ($event->getExceptions() as $exception) {
                    //exception type on an ignored list
                    if (in_array($exception['type'], explode(',', $container->get('sentry.ignore.exception')))) {
                        return;
                    }
                }
                return $event;
            },
        ]);
        /** 
         * @var Auth $auth
         */
        $auth = $container->get(Auth::class);
        if (!$auth->hasIdentity()) {
            return true;
        }
        //configure scopes
        configureScope(function (Scope $scope, Auth $auth): void {
            die('auth');
            //user scope
            $scope->setUser([
                'id' => $auth->getId(),
                'email' => $auth->getEmail(),
                'username' => $auth->getUsername(),
            ]);
        });
        return true;
    }
];