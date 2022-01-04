# A/B Tests

[A/B Tests](./ab-tests.md) provides the tools to carry out randomized experiments using different variations of content features, eg: titles and featured images, and/or different variations of content blocks.

Tests run until the end date or when a statistically significant improvement has been found, when a winner is found the winning variant will be shown to everyone.

Altis provides an A/B Test Block, which you can use to test different variations of a block / set of blocks. And also a developer friendly framework to test different content features like Title and Featured Images, which can be controlled from the Block Editor sidebar.

## Post Title A/B Tests

With this feature enabled it's simple to create A/B Tests for your post titles directly from the post edit screen.

It is enabled by default but can be disabled via the configuration file:

```json
{
	"extra": {
		"altis": {
			"modules": {
				"analytics": {
					"native": {
						"experiments": {
							"titles": false
						}
					}
				}
			}
		}
	}
}
```

Within the post edit screen click on the A/B icon to access the Experiments panel. Here you can set the alternative titles, the amount of traffic to show the tests to as well as start and end dates.

Once you are ready to run the test click on the toggle to unpause it. Results are updated every hour until a statistically significant winner is found.

![A/B Testing Titles user interface](../assets/ab-tests-titles.png)

By default post title A/B tests are enabled for Posts and Pages however custom post types can be supported using the `altis.experiments.titles` key either when registering the post type or in the `init` action, for example:

```php
add_action( 'init', function () {
	add_post_type_support( 'events', 'altis.experiments.titles' );
} );
```

## Creating Custom Tests

There is a programmatic API to register A/B tests for posts:

**`Altis\Analytics\Experiments\register_post_ab_test( string $test_id, array $options )`**

Sets up the test.

- `$test_id`: A unique ID for the test.
- `$options`: Configuration options for the test.
  - `label <string>`: A human readable label for the test.
  - `rest_api_variants_field <string>`: The field name to make variants available at.
  - `rest_api_variants_type <string>`:  The data type of the variants.
  - `goal <string>`: The conversion goal event name, eg "click" or "click:.selector a".
  - `selector <string>`: A CSS selector for a child element to bind the event to.
  - `closest <string>`: A CSS selector for the closest matching parent element to bind the event to. Applied before `selector`.
  - `goal_filter <string | callable>`: Elasticsearch bool query to filter goal results. If a callable is passed it receives the test ID and post ID as arguments.
  - `query_filter <string | callable>`: Elasticsearch bool query to filter total events being queried. If a callable is passed it receives the test ID and post ID as arguments.
  - `variant_callback <callable>`: An optional callback used to render variants based.
    - `$value <mixed>`: The variant value.
    - `$post_id <int>`: The post ID.
    - `$args <array>`: Optional args passed to `output_ab_test_html_for_post()`.
  - `winner_callback <callable>`: An optional callback used to perform updates to the post when a winner is found. Defaults to no-op.
    - `$post_id <int>`: The post ID
    - `$value <mixed>`: The winning variant value.
  - `post_types <array>`: An array of supported post types for the test.

**`output_ab_test_html_for_post( string $test_id, int $post_id, string $default_content, array $args = [] )`**

Returns the A/B Test markup for client side processing.

- `$test_id`: A unique ID for the test.
- `$post_id`: The post ID for the test.
- `$default_content`: The default content for the control test.
- `$args`: An optional array of data to pass through to `variant_callback`.

```php
namespace Altis\Analytics\Experiments;

// Register the test.
register_post_ab_test( 'featured_images', [
	'rest_api_variants_type' => 'integer',
	'goal' => 'click',
	'variant_callback' => function ( $attachment_id, $post_id, $args ) {
		return wp_get_attachment_image(
			$attachment_id,
			$args['size'],
			false,
			$args['attr']
		);
	}
] );

// Apply the test by filtering some standard output.
add_filter( 'post_thumbnail_html', function ( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	return output_ab_test_html_for_post( 'featured_images', $post_id, $html, [
		'size' => $size,
		'attr' => $attr,
	] );
}, 10, 5 );
```

### Goal Tracking

The [goal tracking framework is explained in more detail here](./goal-tracking.md). A built in click event goal is available out of the box which is used by the built in title A/B test feature.

### Variant Data Storage

How you manage the variant data is up to you, for example you could use Fieldmanager, CMB2 or other custom meta boxes framework to save the variant data.

Note you should use the following functions to get and update the variants. All functions are under the `Altis\Analytics\Experiments` namespace:

**`get_ab_test_variants_for_post( string $test_id, int $post_id ) : array`**

**`update_ab_test_variants_for_post( string $test_id, int $post_id, array $variants )`**

### Actions

**`altis.experiments.test.ended: (string) $test_id, (int) $post_id`**

Fired when a test has ended either by finding a statistically significant difference or the end date was reached.

**`altis.experiments.test.ended.$test_id: (int) $post_id`**

This is the same as above but scoped to tests with the ID `$test_id`.

**`altis.experiments.test.winner_found: (string) $test_id, (int) $post_id, (mixed) $value`**

Fired when a winning variant has been found. The `$value` parameter is the stored variant value.

**`altis.experiments.test.winner_found.$test_id: (int) $post_id, (mixed) $value`**

This is the same as the above but scoped to tests with the ID `$test_id`.

## Testing Experiments

In order to see your experiments working locally you need to generate enough traffic to get statistically significant results. You can direct traffic to your development environment using the [Trafficator](https://github.com/humanmade/trafficator) tool.

Install it by running `npm install -g trafficator`.

Create a file called `.trafficator.js` that will perform the necessary actions to trigger conversions. The following example tests the built in `titles` experiment by doing the following:

- generate visits to the home page of your local site
- check the stored tests value, for post ID 1 this is `titles_1`
- return different probabilities for different variant IDs
- click the title link if the probability is met

```js
module.exports = {
	funnels: [
		{
			entry: 'https://my-project.altis.dev/',
			steps: [
				{
					name: 'Click Article Title',
					probability: async page => {
						const p = await page.evaluate(() => {
							const tests = JSON.parse(localStorage.getItem('_altis_ab_tests') || "{}");
							if (tests.titles_1) {
								if (tests.titles_1 === 1) {
									return 0.4;
								}
								if (tests.titles_1 === 2) {
									return 0.1;
								}
							}
							return 0.15;
						});
						return p;
					},
					action: async page => {
						await page.click('.post-1 .entry-title a');
					}
				}
			]
		}
	],
};
```

This should generate traffic that exhibits a trend towards the first title variant.
