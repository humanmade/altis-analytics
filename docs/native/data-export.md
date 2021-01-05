# Data Export

In order to export your analytics data for use in other Business Intelligence tools you can do so using the analytics events API endpoint.

This approach requires requests to be authenticated. The simplest and most secure way to do this is using an application password. You can create one from your [profile page in the admin](admin://profile.php).

## API Endpoint

**`GET /wp-json/analytics/v1/events/<YYYY-MM-DD>`**

Arguments:

- `format`: one of "csv" or "json", defaults to "json".
- `chunk_size`: the number of records to output at a time in the streamed response. A lower number will yield results more quickly but may encounter errors if you have a lot of records. A higher number will yield results more slowly and may result in a timeout or memory issues. Defaults to 3000, max value is 10000.

## Using cURL

To download data in CSV format for a given day using cURL and an application password use the command below with the following replacements:

- `<YYYY-MM-DD>` should be replaced with your target date
- `<username>` should be the user name you created the application password for
- `<password>` should be your application password

```
curl -o <YYYY-MM-DD>.csv \
  -u "<username>:<password>" \
  https://example.com/wp-json/analytics/v1/events/<YYYY-MM-DD>?format=csv
```
