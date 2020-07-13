# Data Structure

All analytics data is in the form of event records stored in Elasticsearch on a set of indexes called `analytics-YYYY-MM-DD` where `YYYY-MM-DD` represents the year, month and day of the data within each index.

New indexes are automatically created every day.

## Key Concepts

The are two key concepts to understand the analytics data itself:

- **Endpoints**
  - Correspond to a unique device & browser combination
  - Can be associated with a known user account via the `Endpoint.User.UserId` property. This is done automatically for logged in users.
  - Persist across sessions
  - Updated via the `Altis.Analytics.updateEndpoint()` function
- **Events**
  - Has to be of a specific "type" eg. "pageView"
  - Can have custom attributes and metrics
  - Data for the current endpoint is merged in
  - Created via the `Altis.Analytics.record()` function

_The most important thing to note is that the endpoint data is merged into events, not provided when recording the events._

## Anatomy of an Event Record

A user session covers every event recorded between opening the website and closing it. For every event recorded the following data is recorded depending on the scope.

- `event_type`: The type of event recorded, eg. `pageView`, `click`, `_session.start` or `_session.stop`.
- `event_timestamp`: The timestamp in milliseconds of when the event was recorded on the site.
- `attributes`: An object of key value pairs. Values must be a single string.
  - `date`: ISO-8601 standard date string.
  - `session`: Unique ID across all page views.
  - `pageSession`: Unique ID for one page view.
  - `url`: The current page URL.
  - `hash`: The current URL hash.
  - `referer`: The page referer.
  - `network`: The current network's primary URL.
  - `networkId`: The current network's Id.
  - `blog`: The current site URL.
  - `blogId`: The current blog ID.
  - `qv_utm_campaign`: The Urchin Tracker campaign from the query string if set.
  - `qv_utm_source`: The Urchin Tracker source from the query string if set.
  - `qv_utm_medium`: The Urchin Tracker medium from the query string if set.
  - `qv_*`: Any query string parameters will be recorded with the prefix `qv_`.
  - Any attributes added via the `altis.analytics.data.attributes` filter.
  - Any attributes added via `Altis.Analytics.registerAttribute()` or passed to `Altis.Analytics.record()`.
- `metrics`: An object of name and value pairs. Values must be numbers.
  - `scrollDepthMax`: Maximum scroll depth on page so far. Percentage value between 1-100.
  - `scrollDepthNow`: Scroll depth at time of event. Percentage value between 1-100.
  - `elapsed`: Time elapsed in milliseconds since the start of the page view.
  - `day`: The day of the week, 1 being Sunday through to 7 being Saturday.
  - `hour`: The hour of the day in 24 hour format.
  - `month`: The month of the year.
  - Any metrics added via `Altis.Analytics.registerMetric()` or passed to `Altis.Analytics.record()`.
- `endpoint`
  - `Id`: A unique UUID for the endpoint.
  - `Address`: An optional target for push notifications such as an email address or phone number.
  - `OptOut`: The push notification channels this visitor has opted out of. Defaults to "ALL".
  - `Attributes`: An object of name and value pairs. Values must be a string or array of strings.
  - `Metrics`: An object of endpoint metrics. This might be used for data like lifetime value.
    - `sessions`: Number of separate browsing sessions for this endpoint.
    - `pageViews`: Total number of page view events recorded for this endpoint.
  - `Demographic`
    - `AppVersion`: Current application version, can be provided via the `altis.analytics.data` filter.
    - `Locale`: Locale code of the endpoint, derived from the browser.
    - `Make`: Make of the current browser / browser engine eg. "Blink".
    - `Model`: Model of the current browser eg "Chrome"
    - `ModelVersion`: Browser version.
    - `Platform`: The device operating system.
    - `PlatformVersion`: The operating system version.
  - `Location`
    - `Country`: The endpoint's country if known / available.
    - `City`: The endpoint's city if known or available.
  - `User`
    - `UserAttributes`
      - Any custom attributes associated with the user if known.
    - `UserId`: An ID associated with the user in your application. Useful for linking endpoints across devices.
- `session`
  - `session_id`: Persists for a subsession, triggered by page visibility changes. Recorded with `_session.start` and `_session.stop` events.
  - `start_timestamp`: Time in milliseconds when the subsession started. Recorded with `_session.start` events.
  - `stop_timestamp`: Time in milliseconds when the subsession ended. Recorded with `_session.stop` events.
  - `duration`: Duration in milliseconds for a subsession. Recorded with `_session.stop` events.

It is important to note that when referencing these fields in a query to use dot notation for nested properties. For example `attributes.date` or `endpoint.Demographic.Model`. When querying string values you should use the `.keyword` suffix whenever exact matches are required.
