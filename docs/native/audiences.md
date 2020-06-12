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
