# Google Tag Manager

Configure the container IDs at a network and per site level by following the below example in your project's `composer.json` file.

```json
{
	"extra": {
		"altis": {
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

**Note** it is not recommended practice to do this, instead you should provide any conditional context you need via [the `dataLayer` variable](data-layer.md) and load all tags from a single container if possible.
