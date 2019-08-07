# Data Layer

Google Tag Manager provides a lot of variables out of the box that can be used in triggers and also in tags themselves however sometimes you need some additional data from the application itself.

You can supply this extra data via GTM's data layer feature.

## The `dataLayer` variable

The `dataLayer` variable is a global JavaScript variable provided on each web page. It is an array containing a single object and is defined in the following way:

```js
var dataLayer = [ {
	property: "value",
	// ... additional properties
} ];
```

The initial value is passed to GTM and can be accessed by creating "dataLayer variables" that are evaluated when the page loads. Additional objects can be pushed onto to the array allowing you to create dynamic triggers based on user interactions or other on page events.

You can explore the data layer by previewing your container and looking in the `dataLayer` tab in the overlay that appears on the site.

## Modifying the initial `dataLayer`

You can use the `hm_gtm_data_layer` filter to modify the initial data layer value. The example below adds a custom post meta value for a custom video post type.

```php
add_filter( 'hm_gtm_data_layer', function ( array $data ) : array {
	if ( is_singular( 'custom-video-post-type' ) ) {
		$data['video_id'] = get_post_meta( get_queried_object_id(), 'video_id', true );
	}

	return $data;
} );
```
