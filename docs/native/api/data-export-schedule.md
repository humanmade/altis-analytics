# Data Export Schedule

Altis can periodically poll raw analytics data and make them available for integrations with external Business Intelligence (BI) tools. The polling happens every 10 minutes and handles a maximum of 100 files per run. The process continues from where it left off on the previous run.

The process happens in the background as a scheduled job, which means there is no performance impact on end users.

This export only runs if an integration is actively subscribing to it, to avoid unnecessary processing of data.

## API

**`altis.analytics.export.cron.enabled`**

Toggle the feature on/off. Worth noting that the feature will be inactive unless a service uses the `altis.analytics.export.data.process` action hook.

Example:

```php
add_filter( 'altis.analytics.export.cron.enabled', '__return_false' );
```

**`altis.analytics.export.cron.frequency`**

Control the interval of the scheduled job, expects a WordPress cron schedule name. See [`cron_schedules`](https://developer.wordpress.org/reference/hooks/cron_schedules/) filter for more information. Defaults to 10 minutes.

Example:

```php
add_filter( 'altis.analytics.export.cron.frequency', function() : string {
    return 'hourly';
} );
```

**`altis.analytics.export.log`**

Use this action to send logs from processing the data via email or any notifications service.

Arguments:

- `message`: Text content of the log message.
- `level`: Level of the error message, following PHP error levels.

Example:

```php
add_action( 'altis.analytics.export.log', 'Acme\log_analytics_messages' );

function log_analytics_messages( string $message, int $level ) : void {
    // Email the log message, or post to Slack for example.
}
```

**`altis.analytics.export.data.process`**

Trigger exporting the raw analytics data to your external BI service.

Arguments:

- `data`: NDJSON formatted string containing a batch of events separated by new lines.

Example:

```php
namespace Acme;

add_action( 'altis.analytics.export.data.process', 'Acme\process_analytics_data' );

function process_analytics_data( string $data ) : void {
    // push the data to external BI tool
}
```

**`altis.analytics.export.max.files`**

Use this action to trigger exporting the raw analytics data to your external BI service.

This might be useful if you are generating a lot of data, resulting in a lag, and need to process it more quickly.

Example:

```php
add_filter( 'altis.analytics.export.max.files', function() : string {
    return 200;
} );
```
