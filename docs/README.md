# Analytics

Analytics is the gathering, understanding and application of data to measure, improve and make sites smarter.

## Google Tag Manager

There are many 3rd party providers of analytics so this module provides a simple interface to add [Google Tag Manager](https://tagmanager.google.com/) containers, the recommended way to add and manage 3rd party JavaScript on your site.

Google Tag Manager provides comprehensive integration with Google Analytics, Google Optimise and other tools in the 360 suite. Additionally there is built in support for services including ChartBeat, comScore, Parse.ly, Hotjar and [many more](https://support.google.com/tagmanager/answer/6106924).

Configure the container IDs at a network and per site level by following the below example in your project's `composer.json` file.

```json
{
	"extra": {
		"platform": {
			"modules": {
				"analytics": {
					"enabled": true,
					"google-tag-manager": {
						"network": "GTM-XXXXXX",
						"sites": {
							"primary-site.com": "GTM-XXXXXX",
							"secondary-site.com": "GTM-XXXXXX"
						}
					}
				}
			}
		}
	}
}
```

Note that the container ID value under `network` is loaded on all sites on the multisite network.

The keys under `sites` are matched against the current hostname. The container with the corresponding ID will be output _only_ on the matched site.

Alternatively the container ID can be set for the network or per site via the CMS admin under the Network Settings and Settings screens respectively.
