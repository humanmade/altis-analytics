# Native Analytics

Altis Analytics provides a built in tool for tracking and analyzing user behavior across the site in real time. It is enabled by default but can be switched off via the configuration file:

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

Altis Analytics can power features such as popular posts widgets, personalisation and AB tests.

## Data Structure

The highly flexible analytics data set is the engine behind features such as [Experience Blocks](https://www.altis-dxp.com/experience-blocks/), [audiences](./audiences.md) and [AB testing](../experiments.md).

[Learn how the data structured in detail here](./data-structure.md).

## Client Side API

[Learn how to use and extend analytics data and audiences in JavaScript here](./client-side-api.md).

## Server Side API

[Learn how to use and extend analytics data and audiences in PHP here](./server-side-api.md).

## Audiences

[Audiences are a powerful tool](./audiences.md) for driving personalisation and gaining insights through testing and reporting.

## Experience Blocks

[Experience Blocks](./experience-blocks.md) not only provide built in editor blocks but also expose the low-level API and HTML markup that powers them.

## Privacy

[Discover how Altis helps to respect your visitor's privacy](./privacy.md) by helping you control how long to keep data for and learning how to process analytics data for long term storage.

## Examples

[Check out the examples](./examples.md) to see some real-world uses of the data.
