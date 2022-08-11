# MMi Sentry Plugin
Sentry plugin compatible with MMi/MMiCms 4.0+

## Usage
* Run $this->container->get('sentry.service'); in your application's AppEventInterceptor::init()
* Above operation can be replaced with $this->container->make('sentry.service'); (nicer look)

### .env configuration:
* SENTRY_DSN=https://your-account-id/sentry.io/your-channel-id
* SENTRY_ENABLED=1
* SENTRY_ENVIRONMENT=TEST
* SENTRY_RELEASE=1.0
