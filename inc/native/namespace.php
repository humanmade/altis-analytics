<?php
/**
 * Native analytics integrations.
 */

namespace Altis\Analytics\Native;

use Altis;
use Altis\Analytics\Experiments;
use Altis\Module;

/**
 * Setup integration.
 */
function bootstrap() {

	$config = Altis\get_config()['modules']['analytics']['native'];

	// Use defaults if `$config` is just set to `true`.
	if ( ! is_array( $config ) ) {
		$module = Module::get( 'analytics' );
		$config = $module->get_settings();
	}

	// Determine whether to display the dashboard replacement.
	define( 'ALTIS_ACCELERATE_DASHBOARD', (bool) ( $config['dashboard'] ?? true ) );

	// Check if Elasticsearch is available before loading.
	if ( ! defined( 'ELASTICSEARCH_HOST' ) ) {
		if ( Altis\get_environment_type() === 'local' ) {
			trigger_error( 'Altis Native Analytics is enabled but Elasticsearch is not configured. You may have the service switched off on your local environment.', E_USER_WARNING );
		} else {
			trigger_error( 'Altis Native Analytics is enabled but Elasticsearch is not configured. Please contact support if you wish to use this feature.', E_USER_WARNING );
		}
		return;
	}

	// Set Analytics plugin to use core Elasticsearch instance.
	add_filter( 'altis.analytics.elasticsearch.url', function ( $url ) {
		if ( function_exists( 'Altis\\Cloud\\get_elasticsearch_url' ) ) {
			return Altis\Cloud\get_elasticsearch_url();
		}
		if ( function_exists( 'Altis\\Enhanced_Search\\get_elasticsearch_url' ) ) {
			return Altis\Enhanced_Search\get_elasticsearch_url();
		}
		return $url;
	} );

	// Set data retention period.
	add_filter( 'altis.analytics.max_index_age', __NAMESPACE__ . '\\set_data_retention_days' );

	// Load Experiments.
	$config['experiments'] = $config['experiments'] ?? true;
	if ( $config['experiments'] ) {
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

	// Add endpoint data from cloudfront headers.
	add_filter( 'altis.analytics.data.endpoint', __NAMESPACE__ . '\\add_endpoint_data' );
}

/**
 * Set the number of days to keep data for.
 *
 * @param int $days The number of days to keep analytics data for.
 * @return int
 */
function set_data_retention_days( int $days ) : int {
	if ( Altis\get_config()['modules']['analytics']['native']['data-retention-days'] ?? false ) {
		return Altis\get_config()['modules']['analytics']['native']['data-retention-days'];
	}
	return $days;
}

/**
 * Add additional endpoint data based on CloudFront headers.
 *
 * @param array $data The endpoint data array.
 * @return array
 */
function add_endpoint_data( array $data ) : array {

	// Add viewer country.
	if ( isset( $_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY'] ) ) {
		if ( ! isset( $data['Location'] ) ) {
			$data['Location'] = [];
		}
		$data['Location']['Country'] = strtoupper( sanitize_key( $_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY'] ) );
	}

	return $data;
}
