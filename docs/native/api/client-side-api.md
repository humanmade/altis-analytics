# Client Side API

The functions are available on the client side in JavaScript to allow extending the default behaviour of the feature beyond recording page views.

**`Altis.Analytics.onReady( callback <function> )`**

Use this function to ensure analytics has loaded before making calls to `Altis.Analytics.registerAttribute()` or `Altis.Analytics.record()` for example.

## Endpoint Functions

**`Altis.Analytics.updateEndpoint( data <object> )`**

Updates the data associated with the current user. Use this to provide updated custom user attributes and metrics, a user ID, and demographic data.

The endpoint data is merged with all new event records associated with that endpoint.

The accepted data shape is as follows:

```json
{
  "Address": string,
  "Attributes": { "key": string[], ... },
  "Demographic": {
    "AppVersion": string,
    "Locale": string,
    "Make": string,
    "Model": string,
    "ModelVersion": string,
    "Platform": string,
    "PlatformVersion": string,
    "Timezone": string
  },
  "Location": {
    "City": string,
    "Country": string,
    "Latitude": number,
    "Longitude": number,
    "PostalCode": string,
    "Region": string
  },
  "Metrics": { "key": number, ... },
  "OptOut": string,
  "User": {
    "UserAttributes": { "key": string[], ... },
    "UserId": string
  }
}
```

**`Altis.Analytics.getEndpoint()`**

Returns the current endpoint data object.

Note that even if attributes, metrics or user attributes are provided as a single string or number they will be returned as an array of that type by this function.

## Tracking Functions

**`Altis.Analytics.record( eventType <string> [, data <object>] )`**

Records an event of `eventType`. This can be any string. The optional data passed in should be an object with either or both an `attributes` object and `metrics` object:

```js
{
  attributes: {
    name: 'value' // <string>
    // ...
  },
  metrics: {
    name: 1 // <number>
    // ...
  },
}
```

Those attributes and metrics can be later queried via Elasticsearch.

## Extending Event Data

**`Altis.Analytics.registerAttribute( name <string>, value <string | callback> )`**

Registers a dynamic attribute value for all events recorded on the page. The value must be a single string, arrays are not supported for event attributes. If a function is passed as the value it will be evaluated whenever an event recorded. Callbacks support `async/await` or returning a `Promise`. It is recommended to cache the results or memoize the function if using asynchronous data.

**`Altis.Analytics.registerMetric( name <string>, value <number | callback> )`**

Similar to `registerAttribute()` above but for metrics.

## Audience Functions

**`Altis.Analytics.updateAudiences()`**

Synchronises the current audiences associated with the page session. You shouldn't ever need to call this manually but it is called any time `updateEndpoint()`, `registerAttribute()` or `registerMetric()` are called. You can hook into the `updateAudiences` event to respond to changes in this data.

**`Altis.Analytics.getAudiences()`**

Retrieves an array of the audience IDs for the current page session.

## Event Hooks

**`Altis.Analytics.on( event <string>, callback <callback> ) : EventListener`**

Attaches and returns an event listener. The available events and their callback arguments are:

- `updateEndpoint`<br />
  Called any time the current endpoint data is updated. The callback receives the endpoint object.<br />
  ```
  Altis.Analytics.on( 'updateEndpoint', function ( endpoint ) {
    console.log( endpoint.Demographic ); // { Platform: 'Mac OS', .... }
  } );
  ```
- `record`:
  Called any time an event is recorded. The callback receives the pinpoint event object.<br />
  ```
  Altis.Analytics.on( 'record', function ( event ) {
    console.log( event.Attributes, event.event_type ); // { referer: '', ... }, 'pageView'
  } );
  ```
- `updateAudiences`<br />
  Called any time the audiences are updated. The callback receives an array of audience IDs.<br />
  ```
  Altis.Analytics.on( 'updateAudiences', function ( audiences ) {
    console.log( audiences ); // [ 1, 2, 3, ... ]
  } );
  ```

**`Altis.Analytics.off( listener <EventListener> )`**

Removes an event listener returned by `Altis.Analytics.on()`.
