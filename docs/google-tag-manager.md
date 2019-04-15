# Google Tag Manager

Configure the container IDs at a network and per site level by following the below example in your project's `composer.json` file.

```json
{
	"extra": {
		"platform": {
			"modules": {
				"analytics": {
					"enabled": true,
					"google-tag-manager": {
						"network": "GTM-XXXXXX",
						"sites": {
							"primary-site.com": "GTM-XXXXXX",
							"secondary-site.com": "GTM-XXXXXX"
						}
					}
				}
			}
		}
	}
}
```

Note that the container ID value under `network` is loaded on all sites on the multisite network.

The keys under `sites` are matched against the current hostname. The container with the corresponding ID will be output _only_ on the matched site.

Alternatively the container ID can be set for the network or per site via the CMS admin under the Network Settings and Settings screens respectively.

## Dynamically setting container IDs

If you need to load alternative containers within the application you can use the following two filters:

```php
// Per site conditional container example.
add_filter( 'hm_gtm_id', function ( string $id ) : string {
	if ( get_current_blog_id() === 3 ) {
		return 'GTM-XXXXXXX';
	}

	return $id;
} );

// Network wide conditional container example.
add_filter( 'hm_gtm_network_id', function ( string $id ) : string {
	if ( is_user_logged_in() ) {
		return 'GTM-XXXXXXX';
	}

	return $id;
} );
```

**Note** it is not recommended practice to do this, instead you should provide any conditional context you need via the `dataLayer` variable described below and load all tags from a single container if possible.

## The `dataLayer` variable

Google Tag Manager provides a lot of variables out of the box that you can use in triggers and also in tags themselves. Often it's useful to have some additional context specific to your application to use in these triggers. You can also supply keys or other values for configuring tags via the data layer.

The `dataLayer` variable is a global variable provided on each webpage and defined in the following way:

```js
var dataLayer = [ {
	property: "value",
	// ... additional properties
} ];
```

The initial value is passed to tag manager and can accessed by creating "dataLayer variables" which are evaluated on load.

You can explore the data layer by previewing your container and looking in the `dataLayer` tab in the overlay that appears on the site.

### Custom event tracking

It's possible to `push` additional objects into the data layer to use as triggers for loading tags dynamically. The following is a manual example:

```js
dataLayer.push( {
	event: "sign-up-click",
	location: "header",
} );
```

You can also enable custom event tracking without writing any JavaScript using special data attributes in your site's markup.

- `data-gtm-on`: _enum_ [click|submit|keyup|focus|blur] The JS event to listen for.
- `data-gtm-event`: _string_ The name or action of the event eg. "play".
- `data-gtm-category`: _string_ Optional group the event belongs to.
- `data-gtm-label`: _string_ Optional human readable label for the event.
- `data-gtm-value`: _number_ Optional numeric value associated with the event eg. product price.
- `data-gtm-fields`: _string_ Optional extra data provided as encoded JSON.
- `data-gtm-var`: _string_ Optionally override the default dataLayer variable name for this event.

Example:

```html
<button
  data-gtm-on="click"
  data-gtm-event="play"
  data-gtm-category="videos"
  data-gtm-label="Featured Promotional Video"
>
  Play video
</button>
```

This feature can be enabled via your `composer.json` by setting the `event-tracking` property to `true` uner the `extra.platform.modules.analytics.google-tag-manager` path.

### Modifying the initial `dataLayer`

You can use the `hm_gtm_data_layer` filter to modify the initial data layer value. The example below adds a custom post meta value for a custom video post type.

```php
add_filter( 'hm_gtm_data_layer', function ( array $data ) : array {
	if ( is_singular( 'custom-video-post-type' ) ) {
		$data['video_id'] = get_post_meta( get_queried_object_id(), 'video_id', true );
	}

	return $data;
} );
```
