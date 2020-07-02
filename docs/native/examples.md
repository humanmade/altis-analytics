# Example Uses

The following examples show how to query the data in ElasticSearch for different use cases. It is important to note the use of the `.keyword` field suffix when comparing strings if you need to make an exact match.

## Recording Current Weather

In this example we want to be able to create audiences based on the current weather where the user is.

The following example uses the db-ip.com API as a data source.

```html
<script src="https://cdn.db-ip.com/js/dbip.js"
	data-api-key="a05p3c1m3n79875p3c1m3nd26cd0n07u5307ac5d">
</script>
<script>
Altis.Analytics.onReady( function () {
	// Registered attributes accept a callback, the callback can return
	// a promise to support fetching asynchronous data.
	Altis.Analytics.registerAttribute( 'weatherCode', function () {
		var weatherCode;
		if ( weatherCode ) {
			return weatherCode;
		}
		return dbip.getVisitor().then( function ( info ) {
			weatherCode = info.weatherCode;
			return info.weatherCode;
		} );
	} );
} );
</script>
```

## Sending Audience IDs To Another Service

Integrating with other analytics is possible using the client side data provided by Altis Analytics. The following example sends the Altis audience ID to Google Analytics as a dimension.

```html
<script>
Altis.Analytics.onReady( function () {
	var audiences = Altis.Analytics.getAudiences();
	// Set configured dimension 12 to the first matched audience ID, 0 for no audience.
	ga( 'set', 'dimension12', audiences[0] || 0 );
} );
</script>
```

## Most Popular Posts

This example uses a terms aggregation to return the most common values in descending order for the given field.

In this case we are looking at all `pageView` events and performing a terms aggregation on the `attributes.postId` field to find the most visited posts.

```php
<?php
$result = Altis\Analytics\Utils\query( [
  // Don't return any hits to keep the response size small.
  'size' => 0,
  'query' => [
    'bool' => [
      'filter' => [
        // Scope the query to the current site.
        [ 'term' => [ 'attributes.blogId.keyword' => get_current_blog_id() ] ],
        // Scope the query to the page view events.
        [ 'term' => [ 'event_type.keyword' => 'pageView' ] ],
        // Fetch events from the last 7 days.
        [ 'range' => [ 'event_timestamp' => [
          'gt' => Altis\Analytics\Utils\milliseconds() - ( DAY_IN_SECONDS * 7 * 1000 ),
        ] ] ],
      ],
    ],
  ],
  // Aggregate data sets
  'aggs' => [
    'top_posts' => [
      // Create buckets by page session ID so all records in each bucket belong
      // to a single user and page session.
      'terms' => [
        'field' => 'attributes.postId.keyword',
        // Get the top 20 posts.
        // By default terms aggregations return the top 10 hits.
        'size' => 20,
      ],
    ],
  ],
  // Order by most recent events first.
  'sort' => [ 'event_timestamp' => 'desc' ],
] );
```

This yields a result like the following:

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
    "total": 1000,
    "max_score": 0
  },
  "aggregations": {
    "top_posts": {
      "doc_count_error_upper_bound": 0,
      "sum_other_doc_count": 0,
      "buckets": [
        { "key": "10", "doc_count": 350 },
        { "key": "13", "doc_count": 207 },
        { "key": "7", "doc_count": 118 },
        ...
      ]
    }
  }
}
```

The objects in the buckets array above have a `key` and a `doc_count` property where the `key` is the value of the `attributes.postId` field and the `doc_count` is the number of times this post ID appeared in the data set.

## Cumulative Statistics

Because data is deleted after the configured number of days outlined in the [privacy document](./privacy.md) it is necessary to gather aggregated data over time using scheduled tasks. This is the method used for [tracking A/B test results](../experiments.md).

The following example records the number of page views for a given post over time.

```php
// Schedule the tracking task when the post is published.
add_action( 'publish_post', function ( $post_id ) {
  if ( ! wp_next_scheduled( 'track_page_views' ) ) {
    wp_schedule_event( time(), 'daily', 'track_page_views', [ $post_id ] );
  }
} );

// Fetch and update the page views on the scheduled action hook.
add_action( 'track_page_views', function ( $post_id ) {
  // Get the existing data if any, default to 0.
  $page_views = get_post_meta( $post_id, 'page_views', true ) ?: 0;
  $page_views_updated = get_post_meta( $post_id, 'page_views_updated', true ) ?: 0;

  // Get the page views events.
  $result = Altis\Analytics\Utils\query( [
    // Don't return any actual data, we just want to know the total.
    'size' => 0,
    // Restrict the results to a single post we're interested in.
    'query' => [
      'bool' => [
        'filter' => [
          // Scope the query to the current site.
          [ 'term' => [ 'attributes.blogId.keyword' => get_current_blog_id() ] ],
          // Scope the query to page view events.
          [ 'term' => [ 'event_type.keyword' => 'pageView' ] ],
          // Filter by the target post ID.
          [ 'term' => [ 'attributes.postId.keyword' => $post_id ] ],
          // Only get events more recent than the last update (milliseconds).
          [ 'range' => [ 'event_timestamp' => [ 'gte' => $page_views_updated ] ] ],
        ],
      ],
    ],
  ] );

  // Set the last updated time to time now in milliseconds.
  update_post_meta( $post_id, 'page_views_updated', Altis\Analytics\Utils\milliseconds() );

  // Add the total number of records to our current count.
  update_post_meta( $post_id, 'page_views', $page_views + $result['hits']['total'] );
} );
```

The `page_views` post meta can then be used to display in post list tables, or used in queries for example:

```php
$posts_with_less_than_10_views = new WP_Query( [
  'meta_key' => 'page_views',
  'meta_value_num' => 10,
  'meta_compare' => '<=',
] );
```

## Time On Page Stats

The following query will output aggregated statistics for the time spent on the given page URL across all visitor sessions.

The stats aggregation will return values including minimum, maximum, average, total and the standard deviation of the distribution.

The stats aggregation values can be stored in the database and cumulatively added to using the [`Altis\Analytics\Utils\merge_aggregations()` function](./server-side-api.md#helper-functions).

```php
<?php
$result = Altis\Analytics\Utils\query( [
  // Don't return any hits to keep the response size small.
  'size' => 0,
  // Restrict the results to a single page we're interested in.
  'query' => [
    'bool' => [
      'filter' => [
        // Scope the query to the current site.
        [ 'term' => [ 'attributes.blogId.keyword' => get_current_blog_id() ] ],
        // Filter by the current page URL.
        [ 'term' => [ 'attributes.url.keyword' => 'https://example.com/page' ] ],
      ],
    ],
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
          'sum' => [ 'field' => 'session.duration' ],
        ],
      ],
    ],
    // Create a stats aggregation for all the time on page values found above.
    'stats' => [
      'stats_bucket' => [
        'buckets_path' => 'sessions>time_on_page',
      ],
    ],
  ],
  // Order by latest events.
  'sort' => [ 'event_timestamp' => 'desc' ],
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
