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

	// Load Experiments.
	if ( $config['experiments'] ) {
		load_experiments();

		// Enable/Disable Experiments Features.
		if ( is_array( $config['experiments'] ) ) {
			foreach ( $config['experiments'] as $feature => $enabled ) {
				add_filter(
					"altis.experiments.features.{$feature}",
					$enabled ? '__return_true' : '__return_false'
				);
			}
		}
	}
}

/**
 * Load Experiments plugin.
 */
function load_experiments() {
	require_once ROOT_DIR . '/vendor/altis/experiments/plugin.php';
}
