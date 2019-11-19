# Remarketing

Remarketing can be configured through a custom script tag, the Google Ads remarketing tag or through Google Analytics. The latter approach is recommended if you are using GA.

Once you have Google Analytics and [conversion tracking](conversion-tracking.md) configured in Tag Manager you can use that information within Google Analytics to enable remarketing and create Audience Definitions.

The audiences you define based on previous conversions or other desirable behaviors can be used to set cookies that are easily shared with Google Ads, AdSense, Ad Exchange or Postbacks (for 3rd party ad providers) to show more relevant ads and offers.

You should link your ad provider accounts to the current GA property from the GA admin section. These will later be available to select as "Audience Destinations".

In the GA admin section:

1. Select "Audience Definitions" and then the "Audiences" sub option
2. Follow the steps there to enable remarketing. A default "All users" audience will be created for you. 
3. Click the blue "Enable" button

The Audiences page will now show you a list of your audiences, and you can create new audiences here based many facets including:

- Goal conversions
- Smart lists (predictive remarketing managed by Google)
- Event sequences
- Demographics
- Returning or new visitors

For each audience you create you can then select the destinations for them, such as Google Ads.
