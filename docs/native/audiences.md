# Audiences

Audiences are user-defined categories of users, based on conditions related to their analytics data.

Audiences allow for the creation of conditions to narrow down event queries or endpoints but also can be used for determining effects on the client side.

[Experience Blocks](https://www.altis-dxp.com/experience-blocks/) make use of audience data to personalise content for example.

## Mapping Event Data

To enable the use of any event data in the audience editor it needs to be mapped to a human readable label using the `Altis\Analytics\Audiences\register_field()` function:

```php
use Altis\Analytics\Audiences;

add_action( 'init', function () {
  Audiences\register_field(
    'endpoint.Location.City', // The Elasticsearch field to query.
    __( 'City' ), // A label for the field.
    __( 'The closest metropolitan area.' ) // Optional description for the field.
  );
} );
```

In the above example the 1st parameter `endpoint.Location.City` represents the field in the event record to query against. Other examples include `attributes.qv_utm_campaign` or `endpoint.User.UserAttributes.custom` for example.

The 2nd parameter is a human readable label for the audience field, the 3rd parameter is an optional description to provide further context for the data.

## Default Audience Fields

The following fields are already mapped by default:

- Referrer
- Browser
- Browser version
- Browser locale (language setting)
- Operating system
- Operating system version
- Country code
- UTM Campaign
- UTM Source
- UTM Medium
- UTM Term
- UTM Content
- Hour
- Day
- Month
- Year
- Sessions (unique sessions for the endpoint)
- Page views (total page views for the endpoint)

## Audience Picker React Component

The analytics module exposes a React component that can be used when writing blocks for the block editor. This is useful if you want to write custom personalisation features.

To get started you must first enqueue the audience assets. This can be done by either enqueuing the registered script handle `altis-analytics-audience-ui` directly or adding the script as a dependency of your block assets as in the example below:

```php
add_action( 'enqueue_block_editor_assets', function () {
  wp_enqueue_script(
    'my-block',
    plugins_url( 'my-block.js', __FILE__ ),
    [
      // Add the analytics UI script as a dependency.
      'altis-analytics-audience-ui',
    ]
  );
} );
```

Using the component will then look like the following:

```js
// Import the component from the global Altis.Analytics.components object.
const { AudiencePicker } = Altis.Analytics.components;

// Block editor edit component.
const Edit = props => {
  return (
    <>
      <InspectorControls>
        <PanelBody title={ 'Block controls' }>
          <AudiencePicker
            label={ __( 'Audience' ) }
            audience={ props.attributes.audience }
            onSelect={ audienceId => props.setAttributes( { audience: audienceId } ) }
            onClearSelection={ () => props.setAttributes( { audience: null } ) }
          />
        </PanelBody>
      </InspectorControls>
      <div className="my-block">
        ... block content ...
      </div>
    </>
  );
}
```

The component props are:

- **`label`**: A text label for the input.
- **`audience`**: The currently selected audience ID.
- **`onSelect`**: Callback to fire when an audience is selected. Passes the audience ID as the first argument and the full audience post object as the second argument.
- **`onClearSelection`**: Callback to fire when the audience picker selection is cleared.
