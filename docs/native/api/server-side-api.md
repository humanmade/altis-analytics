# Server Side API

A set of PHP functions are available to assist in working with analytics data.

## Constants

**`ALTIS_ANALYTICS_ELASTICSEARCH_URL`**

Allows you to define the Elasticsearch server URL directly.

**`ALTIS_ANALYTICS_LOG_QUERIES`**

Set this to `true` to enable logging queries to the error log.

## Querying Functions

**`Altis\Analytics\Utils\query( array $query, array $params = [] ) : array`**

Queries the analytics data with the Elasticsearch Query DSL provided in `$query`. `$params` will be added as query string parameters to the Elasticsearch request URL.

**`Altis\Analytics\Utils\get_elasticsearch_url() : string`**

Get the Elasticsearch server URL.

## Audience Functions

**`Altis\Analytics\Audiences\register_field( string $field, string $label, ?string $description = null )`**

Registers a field in the event record data to make it available in the audiences UI. Registered fields can be used to define audiences that can be used when querying analytics data.

`$field` is a field in the ElasticSearch index as described in the [data structure](./data-structure.md), `$label` is a human readable name for the field, and `$description` is an optional extended description shown to users in the audience builder UI.

**`Altis\Analytics\Audiences\query_audiences( array $args = [] ) : array`**

Returns audience post objects sorted by priority. The optional `$args` array is passed to `WP_Query()`.

**`Altis\Analytics\Audiences\build_audience_query( array $audience ) : array`**

Given an audience configuration array returns a PHP array that can be used to query events in ElasticSearch.

The `$audience` configuration should have the following shape:

```php
$audience = [
	'include' => 'all' // <enum> all|any, determines if all groups should match.
	'groups' => [
		[
			'include' => // <enum> all|any
			'rules' => [
				[
					'field' => 'endpoint.Demographic.Model', // <string> The field to query.
					'operator' => '=', // <enum> =, !=, *=, ^=, !*, gte, lte, gt, lt
					'value' => 'Chrome', // <string|number> The value to compare against.
				],
				// ...
			],
		],
		// ... additional groups.
	],
];
```

## Helper Functions

**`Altis\Analytics\Utils\milliseconds() : integer`**

Get the current time since the unix epoch in milliseconds.

**`Altis\Analytics\Utils\merge_aggregations( array $current, array $new, string $bucket_type = '' ) : array`**

A utility function for merging aggregations returned by Elasticsearch queries. This is necessary to keep a store of your analytics data as Elasticsearch indexes can be rotated or lost.

The `$bucket_type` parameter should correspond to the type of aggregation you are merging data for. For simple results you can leave this out however for nested aggregations or to determine the bucket type automatically you should make your queries to ElasticSearch with the `typed_keys` query parameter. This helps the `merge_aggregations()` function perform more complex merges of nested aggregate data.

```php
$result = Altis\Analytics\Utils\query( [
  // ... your query with aggregations ...
], [
  // Return aggregation type with keys.
  'typed_keys' => '',
] );
```

## Filters

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

**`altis.analytics.noop <bool>`**

Returning `true` from this filter will prevent any events or updated endpoint data from being sent to Pinpoint. The built in usage for this is to prevent logging events on page previews.

**`altis.analytics.max_index_age <int>`**

Filter the maximum number of days to keep real time stats available for. The default number of days is 14, after which data is removed. This is important for streamlining your user's privacy.

Insights and aggregated analytics data can be calculated, updated and stored in the database in cases where you wish to retain information for longer periods of time such as number of page views.

**`altis.analytics.audiences.limit <int>`**

Changes the default limit on the number of currently active audiences. The default is 20, sorted in order of priority.
