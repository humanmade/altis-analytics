<?php
/**
 * Analytics Module functions.
 *
 * @package altis/analytics
 */

namespace Altis\Analytics;

use Altis\Module;
use const Altis\ROOT_DIR;

/**
 * Setup function for the anlaytics module.
 *
 * @param Module $module The module object.
 */
function bootstrap( Module $module ) {
	$settings = $module->get_settings();

	if ( $settings['google-tag-manager'] ) {
		add_action( 'muplugins_loaded', __NAMESPACE__ . '\\load_google_tag_manager', 0 );
	}

	if ( $settings['native'] ) {
		add_action( 'muplugins_loaded', __NAMESPACE__ . '\\load_native_analytics', 0 );
	}

	// Check if the Segment integration is enabled, and whether a key has been added to config.
	if (
		! empty( $settings['integrations']['segment'] )
		&& ( $settings['integrations']['segment']['enabled'] ?? true ) === false
	) {
		// Define the constant of the API key from config, which activates the integration.
		if ( ! empty( $settings['integrations']['segment']['api_key'] ) && ! defined( 'SEGMENT_API_WRITE_KEY' ) ) {
			define( 'SEGMENT_API_WRITE_KEY', $settings['integrations']['segment']['api_key'] );
		}
	}
}

/**
 * Loads Google Tag Manager plugin and related code.
 */
function load_google_tag_manager() {
	require_once ROOT_DIR . '/vendor/humanmade/hm-gtm/plugin.php';
	Google_Tag_Manager\bootstrap();
}

/**
 * Load native analytics features.
 *
 * @return void
 */
function load_native_analytics() {
	require_once ROOT_DIR . '/vendor/altis/aws-analytics/plugin.php';
	Native\bootstrap();
}
