
# Goal Tracking

Conversions goals help you measure the performance of aspects of your site. With Altis Native Analytics you can use the [client-side API](./client-side-api.md) to track any events you wish, however, some goal tracking is integrated into the [Experiments](./experiments.md) and [Experience Blocks](./experience-blocks.md) features.

Conversion goals allow us to determine whether an A/B test variant has been successful or not and whether some personalised content is out performing others. The goal conversion rate is generally calculated as the `number of conversions / number of impressions`.

## Registering Goals

Registered goals are available to select in the admin when configuring experience blocks. A few are provided out of the box however additional goals can be added either in PHP, JavaScript, or both.

### PHP

**`Altis\Analytics\Experiments\register_goal( string $name, array $args = [] )`**

- `$name` is a string ID you can use to reference the goal later.
- `$args` is an array of options:
  - `string $args['label']`: A human readable label for the goal. Defaults to $name.
  - `string $args['event']`: The JS event to trigger on. Defaults to $name.
  - `string $args['selector']`: An optional CSS selector to scope the event to.
  - `string $args['closest']`: An optional CSS selector for finding the closest matching parent to bind the event to.
  - `string $args['validation_message']` An optional message to show if the variant is missing the selector.
### JavaScript

Goals registered client side behave a little differently. They can be completely custom goals that are not registered in PHP but they can also be used to enhance server-side registered goals by defining the callback used to bind the event listener.

To define a own goal handlers in JavaScript use the following function:

**`Altis.Analytics.Experiments.registerGoalHandler( name <string>, callback <function>, closest <array> )`**

If `name` matches a server-side registered goal the default callback and optionally `closest` value for binding the event listener are overridden.

The callback receives the following parameters:

- `element <HTMLElement>`: Target node for the event.
- `record <function>`: Receives the target element and a callback to log the conversion.

```js
Altis.Analytics.onReady( function () {
    Altis.Analytics.Experiments.registerGoalHandler( 'scrollIntoView', function ( element, record ) {
        var listener = function ( event ) {
            // Check element has come into view or not.
            if ( element.getBoundingClientRect().top > window.innerHeight ) {
                return;
            }

            // Remove event listener immediately.
            window.removeEventListener( 'scroll', listener );

            // Record event.
            record( event );
        };

        // Start listening to scroll events.
        window.addEventListener( 'scroll', listener );
    } );
} );
```

**Note:** This JavaScript should be enqueued in the `<head>` via the `wp_enqueue_scripts` action.

While it is possible to record an event directly in the goal handler it's primary purpose is to handle binding the event listener and calling the `record()` function that is passed to it. This is because the place where the `record()` function is defined may have access to useful data that should be tracked with the event.

## Scoped Event Handling

When goals are processed they are typically bound to an element in the current scope, for instance with Experience Blocks it would be bound to the block DOM node itself.

To gain more control over the elements that the event is bound to you can specify the `selector` and `closest` parameters depending on your use-case.

For example, the built in goal for "Click on any link" is registered like so with the selector `a`:

```php
namespace Altis\Analytics\Experiments;

register_goal( 'click_any_link', [
    'label' => __( 'Click any link' ),
    'event' => 'click',
    'selector' => 'a',
] );
```

For the built titles A/B test feature the selector is empty but `closest` is set to `a` because it is replacing only the post title text and not the markup around it.
