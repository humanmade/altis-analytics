<?php
/**
 * Native analytics integrations.
 */

namespace Altis\Analytics\Native;

use const Altis\ROOT_DIR;
use function Altis\Enhanced_Search\get_elasticsearch_url;
use function Altis\get_config;

/**
 * Setup integration.
 */
function bootstrap() {
	$config = get_config()['modules']['analytics']['native'];

	// Set Analytics plugin to use core Elasticsearch instance.
	add_filter( 'altis.analytics.elasticsearch.url', function () {
		return get_elasticsearch_url();
	} );

	// Load AB Tests.
	if ( $config['ab-tests'] ) {
		load_ab_tests();

		// Enable/Disable AB Test Features.
		if ( is_array( $config['ab-tests'] ) ) {
			foreach ( $config['ab-tests'] as $feature => $enabled ) {
				add_filter(
					"altis.ab_tests.features.{$feature}",
					$enabled ? '__return_true' : '__return_false'
				);
			}
		}
	}
}

/**
 * Load AB Tests plugin.
 */
function load_ab_tests() {
	require_once ROOT_DIR . '/vendor/altis/ab-tests/plugin.php';
}
