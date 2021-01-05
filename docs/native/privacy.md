# Privacy

Altis Analytics by default does not record visitor IP addresses to help protect privacy.

## Cookie Consent

Altis Analytics integrates natively with the [Altis Privacy module Consent feature](docs://privacy/consent/README.md) and requires the `statistics-anonymous` category to be consented to at a minimum.

While the `statistics-anonymous` consent category is opted into by default that can be changed via a filter if you have strict requirements regarding tracking.

```php
// Only allow functional cookies by default.
add_filter( 'altis.consent.allowlisted_categories', function () {
	return [ 'functional' ];
} );
```

Without the `statistics` category of consent the `endpoint.User` data will be stripped from the data sent to Altis Analytics. To help protect your user's privacy any personally identifiable information should only ever be stored under the `endpoint.User.UserAttributes` property. You can read more about this in the section on [Data Structure](./data-structure.md).

## Data Retention

Analytics data is stored in 2 locations:

- S3 backup
- Elasticsearch

S3 backup data older than 90 days is automatically removed. Elasticsearch data is retained by default for 14 days. This value can be configured up to a maximum of 60 days.

Insights and metrics can be calculated and updated using scheduled tasks to keep track of data such as the number of page views over time without needing to query the entire history of analytics data. This will keep queries faster and help to protect you visitor's privacy, and can also eliminate the need for requiring visitor's to opt in in the same way Google Analytics does.

To configure the data retention time the following configuration option is available:

```json
{
	"extra": {
		"altis": {
			"modules": {
				"analytics": {
					"native": {
						"data-retention-days": 7
					}
				}
			}
		}
	}
}
```

The `data-retention-days` property accepts an integer between 1 and 60. Data older than the number of days will be automatically deleted at midnight every day.

## Collecting Long Term Data

For an example of collecting and storing over longer periods of time see the [cumulative statistics example](./examples.md#cumulative-statistics).
