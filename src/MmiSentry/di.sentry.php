<?php

use Psr\Container\ContainerInterface;
use Sentry\Event;

use function DI\env;
use function Sentry\init;

return [
    'sentry.dsn'                => env('SENTRY_DSN', ''),
    'sentry.environment'        => env('SENTRY_ENVIRONMENT', 'LOCAL'),
    'sentry.ignore.exception'   => env('SENTRY_IGNORE_EXCEPTION', 'Mmi\Mvc\MvcNotFoundException,Mmi\Mvc\MvcForbiddenException,ErrorException'),
    'sentry.ignore.code'        => env('SENTRY_IGNORE_CODE', ''),
    'sentry.enabled'            => env('SENTRY_ENABLED', 0),
    'sentry.release'            => env('SENTRY_RELEASE', ''),

    'sentry.service' => function (ContainerInterface $container): bool {
        //sentry disabled
        if (!$container->get('sentry.enabled')) {
            return false;
        }
        //defining ignored
        define('SENTRY_IGNORED_EXCEPTIONS', explode(',', $container->get('sentry.ignore.exception')));
        define('SENTRY_IGNORED_CODES', explode(',', $container->get('sentry.ignore.code')));
        //sentry initialization
        init([
            'dsn' => $container->get('sentry.dsn'),
            'release' => $container->get('sentry.release'),
            //prevent quota issues
            'traces_sample_rate' => 1.0,
            //environment - configured, or guessed by config class name
            'environment' => $container->get('sentry.environment'),
            //before send event
            'before_send' => function (Event $event) {
                //iterate exceptions
                foreach ($event->getExceptions() as $exception) {
                    //exception type on an ignored list
                    if (in_array($exception->getType(), SENTRY_IGNORED_EXCEPTIONS)) {
                        return null;
                    }
                    if (
                        isset($exception->getMechanism()->getData()['code']) &&
                        in_array($exception->getMechanism()->getData()['code'], SENTRY_IGNORED_CODES)
                    ) {
                        return null;
                    }
                }
                return $event;
            },
        ]);
        return true;
    },
];
