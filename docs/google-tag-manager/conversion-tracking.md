# Conversion tracking

Conversion tracking (or custom event tracking) can be set up via Google Tag Manager. While the events can be used to trigger any sort of tag the most common use case is to pass these events to a Google Analytics tag.

## Tag Manager Setup

In GTM do the following steps:

1. Create 3 new variables of type "Data Layer Variable" and in the "Data Layer Variable name" field enter the following:
  - category
  - label
  - value
2. Create a new trigger of type "Custom Event".
  - You can specify certain events or you can leave the default setting to trigger on all custom events to log everything to Google Analytics.
3. Create a new tag of type Google Analytics and add the following configuration:
  - Choose "Event" as the tracking type
  - For category click the "+" icon and select your category variable from above
  - Repeat the above for the label and value fields
  - For the action value enter `{{Event}}`, this is a built in variable containing the event name
  - Set "Non-interaction event" to false
  - Select your Google Analytics settings variable in the "Google Analytics Settings" field. You may need to create this if you haven't already
  - At this stage you can optionally choose to override the GA settings to add custom dimensions or metrics based on other data layer variables you have added
4. Publish your changes!

Once you have published this tag all custom events will be passed to Google Analytics. From there you can configure audience definitions and goals using your custom events.

The final steps happen within Google Analytics to configure conversion goals based on your events.

### Google Analytics Setup

From the Google Analytics admin section:

1. Go to "Goals" in the view column
2. Create a new goal and enter a title
3. Select "Event" as the goal type and click continue
4. Enter the values for the custom event you want to match on, you can use any combination of Action, Category, Label and Value

Your conversion goals then show up in your dashboard, and can subsequently be used for [remarketing](remarketing.md) and also for creating [Google Optimize](optimize.md) content tests.

## Manual Event Tracking

It's possible to `push` additional objects into the data layer to use as triggers for loading tags dynamically. The following is a manual example:

```js
dataLayer.push( {
	event: "sign-up-click",
	location: "header",
} );
```

## Automatic Event Tracking

You can set up custom event tracking without writing any additional JavaScript by using the following data attributes in your site's markup.

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

This feature can be toggled via your `composer.json` by setting the `event-tracking` property to `true` or `false`.

```json
{
	"extra": {
		"altis": {
			"modules": {
				"analytics": {
					"google-tag-manager": {
						"event-tracking": true
					}
				}
			}
		}
	}
}
```
