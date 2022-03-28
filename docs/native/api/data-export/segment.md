# Segment.com Integration

Altis Analytics includes native integration for Segment.com, which when activated pushes analytics data to Segment for further tracking and analysis.

To activate the integration, an API key needs to be obtained from Segment's dashboard. Navigate to _Sources > {Your source for the current site} > Settings > API Keys_, and grab the _Write Key_ value. Add it to your Altis configuration in `composer.json` like so:

```json
{
	"extra": {
		"altis": {
			"modules": {
				"analytics": {
					"integrations": {
						"segment": {
							"enabled": true,
							"api_key": "YOUR_WRITE_API_KEY_HERE"
						}
					}
				}
			}
		}
	}
}
```

You should now start to see Altis Analytics data in your Segment dashboard. Events will be synced every 10 minutes by default (refer to [the Data Export Schedule documentation](./README.md#data-export-schedule) for how to change the default schedule).

## API

### Functions

**`register_segment_group_field( string $field, array $traits = [] ) : void`**

Registers a grouping field, which is sent to Segment as a group call. See [Segment docs on Group cals](https://segment.com/docs/connections/sources/catalog/libraries/server/http-api/#group) for more information.

- *`$field` is the event field to group by.
- *`$traits` is the data map to add to the group from.

```php
register_segment_group_map( 'endpoint.Attributes.AudienceId', [ 'country' => 'endpoint.Attributes.UserAttributes.country' ] )
```

### Actions

**`altis.analytics.segment.request_failure : Requests_Exception|Requests_Response $response,  Array $batch`**

Triggered when a batch request to Segment APIs fails.

**`altis.analytics.segment.request_success  : Requests_Response $response,  Array $batch`**

Triggered when a batch request to Segment APIs succeeds.

**`altis.analytics.segment.after_send : Array<Requests_Exception|Requests_Response> $results, Array $batches, Array<Array> $events`**

Triggered after sending the batch request to Segment APIs.

### Filters

**`altis.analytics.segment.api_write_key : String`**

Filters the Segment API key, for instances where different keys are needed for different sites. Use the constant `SEGMENT_API_WRITE_KEY` otherwise.

**`altis.analytics.segment.mapping : Array`**

Filters the mapping tree which the transformer uses to translate the Altis Analytics event format to the format needed by Segment. Use only if you need to customize the event structure that will be logged by Segment. The filter accepts a second parameter `$type <string>` that indicates the event type, eg: `identify`, `track`, `group`, etc.

**`altis.analytics.segment.formatted_data : Array`**

Filters the formatted data, in case further customizations are needed.

**`altis.analytics.segment.groups : Array`**

Filters the group definitions, for advanced use only, use the function `register_segment_group_map()` instead for simple usage.
