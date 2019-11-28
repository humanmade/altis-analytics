# Native Analytics

Altis Analytics provides a built in tool for tracking and analyzing user behavior across the site. It is enabled by default but can be switched off via the configuration file:

```json
{
	"extra": {
		"altis": {
			"modules": {
				"analytics": {
					"native": false
				}
			}
		}
	}
}
```

Analytics data is stored in an S3 data lake and delivered to Elasticsearch where it is 1-5 minutes behind real-time.

The data is queried from Elasticsearch in the application providing a powerful query language with filtering and aggregations for collecting statistics and other metrics.

## Key Concepts

The are two key concepts to understand the analytics data:

- **Endpoints**
  - Correspond to a unique device & browser combination
  - Can be associated with a known user account via the `Endpoint.User.UserId` property. This is done automatically for logged in users.
  - Persist across sessions
  - Updated via the `Altis.Analytics.updateEndpoint()` function
- **Events**
  - Has to be of a specific "type" eg. "pageView"
  - Can have custom attributes and metrics
  - Data for the current endpoint is merged in
  - Created via the `Altis.Analytics.record()` function

_The most important thing to note is that the endpoint data is merged into events, not provided when recording the events._

## Usage

### JavaScript Functions

**`Altis.Analytics.updateEndpoint( data <object> )`**

Updates the data associated with the current user. Use this to provide updated custom user attributes and metrics, a user ID, and demographic data.

The endpoint data is merged with all new event records associated with that endpoint.

**`Altis.Analytics.record( eventType <string> [, data <object>] )`**

Records an event of `eventType`. This can be any string. The optional data passed in should be an object with either or both an `attributes` object and `metrics` object:

```js
{
  attributes: {
    name: 'value' // <string>
    // ...
  },
  metrics: {
    name: 1 // <number>
    // ...
  },
}
```

Those attributes and metrics can be later queried via Elasticsearch.

**`Altis.Analytics.registerAttribute( name <string>, value <string | callback> )`**

Sometimes you may want to record a dynamic attribute value for all events on the page. The `registerAttribute()` allows this. If a function is passed as the value will be evaluated at the time an event recorded.

**`Altis.Analytics.registerMetric( name <string>, value <string | callback> )`**

Similar to `registerAttribute()` above but for metrics.

### Filters

The plugin provides a few hooks for you to control the default endpoint data and attributes recorded with events.

**`altis.analytics.data.endpoint <array>`**

Allows you to provide server-side data used to update the visitors associated endpoint with custom attributes and demographic data. Mostly useful for providing extra data for logged in sessions such as IP based location data.

**`altis.analytics.data.attributes <array>`**

Allows you to provide server-side data recorded for all events on the page. Useful if you need to query records based on the page context.

**`altis.analytics.data.metrics <array>`**

Similar to the `altis.analytics.data.attributes` filter but allows you to pass metrics based on server-side data.

**`altis.analytics.data <array>`**

Filters the entire array passed to the client side.

**`altis.analytics.elasticsearch.url <string>`**

Filters the Elasticsearch server URL.

### PHP Functions

**`Altis\Analytics\Utils\query( array $query, array $params = [] ) : array`**

Queries the analytics data with the Elasticsearch Query DSL provided in `$query`. `$params` will be added as query string parameters to the Elasticsearch request URL.

**`Altis\Analytics\Utils\get_elasticsearch_url() : string`**

Get the Elasticsearch server URL.

**`Altis\Analytics\Utils\milliseconds() : integer`**

Get the current time since the unix epoch in milliseconds.

**`Altis\Analytics\Utils\merge_aggregations( array $current, array $new, string $bucket_type = '' ) : array`**

A utility function for merging aggregations returned by Elasticsearch queries. This is necessary to keep a store of your analytics data as Elasticsearch indexes can be rotated or lost.

## Querying Data

All analytics data is in the form of event records stored in Elasticsearch on an index called `analytics`.

### Anatomy of an event record

A user session covers every event recorded between opening the website and closing it. For every event recorded the following data is recorded depending on the scope.

- `event_type`: The type of event recorded, eg. "pageView", "click", "_session.start" or "_session.stop".
- `event_timestamp`: The timestamp in milliseconds of when the event was recorded on the site.
- `attributes`: String data associated with event.
  - `session`: Unique ID across all page views.
  - `pageSession`: Unique ID for one page view.
  - `url`: The current page URL.
  - `hash`: The current URL hash.
  - `referer`: The page referer.
  - `utm_campaign`: The Urchin Tracker campaign from the query string if set.
  - `utm_source`: The Urchin Tracker source from the query string if set.
  - `utm_medium`: The Urchin Tracker medium from the query string if set.
  - Any attributes added via the `altis.analytics.data.attributes` filter.
  - Any attributes added via `Altis.Analytics.registerAttribute()` or passed to `Altis.Analytics.record()`.
- `metrics`: Numeric data associated with an event.
  - `scrollDepthMax`: Maximum scroll depth on page so far. Percentage value between 1-100.
  - `scrollDepthNow`: Scroll depth at time of event. Percentage value between 1-100.
  - `elapsed`: Time elapsed in milliseconds since the start of the page view.
  - Any metrics added via `Altis.Analytics.registerMetric()` or passed to `Altis.Analytics.record()`.
- `endpoint`: Represents the current device and browser.
  - `Address`: An optional target for push notifications such as an email address or phone number.
  - `OptOut`: The push notification channels this visitor has opted out of. Defaults to "ALL".
  - `Demographic`
    - `AppVersion`: Current application version, can be provided via the `altis.analytics.data` filter.
    - `Locale`: Locale code of the endpoint, derived from the browser.
    - `Make`: Make of the current browser / browser engine eg. "Blink".
    - `Model`: Model of the current browser eg "Chrome"
    - `ModelVersion`: Browser version.
    - `Platform`: The device operating system.
    - `PlatformVersion`: The operating system version.
  - `User`: If there is a user account for this endpoint associated information can be found here.
    - `UserAttributes`
      - Any custom attributes associated with the user if known.
    - `UserId`: An ID associated with the user in your application. Useful for linking endpoints across devices.
- `session`: Sub-session tracking eg. page visibility change events.
  - `session_id`: Persists for a subsession, triggered by page visibility changes. Recorded with `_session.start` and `_session.stop` events.
  - `start_timestamp`: Time in milliseconds when the subsession started. Recorded with `_session.start` events.
  - `stop_timestamp`: Time in milliseconds when the subsession ended. Recorded with `_session.stop` events.
  - `duration`: Duration in milliseconds for a subsession. Recorded with `_session.stop` events.

### Time on page stats example

```php
<?php
$result = Altis\Analytics\Utils\query( [
  // Don't return any hits to keep the response size small.
  'size' => 0,
  // Restrict the results to a single page we're interested in.
  'query' => [
    'bool' => [
      'filter' => [
        [ 'term' => [ 'attributes.url.keyword' => 'https://example.com/page' ] ]
      ]
    ]
  ],
  // Aggregate data sets
  'aggs' => [
    'sessions' => [
      // Create buckets by page session ID so all records in each bucket belong
      // to a single user and page session.
      'terms' => [
        'field' => 'attributes.pageSession.keyword',
        // Use data from the top 100 unique page sessions.
        // By default terms aggregations return the top 10 hits.
        'size' => 100
      ],
      // Sub aggregations.
      'aggs' => [
        // Get the time on page by summing all session.duration values for the page session.
        'time_on_page' => [
          'sum' => [ 'field' => 'session.duration' ]
        ]
      ]
    ],
    // Create a stats aggregation for all the time on page values found above.
    'stats' => [
      'stats_bucket' => [
        'buckets_path' => 'sessions>time_in_page'
      ]
    ]
  ],
  // Order by latest events.
  'sort' => [ 'event_timestamp' => 'desc' ]
] );
```

The output will look something like the following:

```json
{
  "took": 15,
  "timed_out": false,
  "_shards": {
    "total": 5,
    "successful": 5,
    "skipped": 0,
    "failed": 0
  },
  "hits": {
    "total": 329,
    "max_score": 0
  },
  "aggregations": {
    "sessions": {
      "doc_count_error_upper_bound": 0,
      "sum_other_doc_count": 0,
      "buckets": [
        { "...": "..." }
      ]
    },
    "stats": {
      "count": 92,
      "min": 0,
      "max": 4963998,
      "avg": 78251.14130434782,
      "sum": 7199105
    }
  }
}
```

You can further trim the size of the returned response using the `filter_path` query parameter. For example if we're only interested in the stats aggregation we can set `filter_path=-aggregations.sessions` to remove it from the response.
