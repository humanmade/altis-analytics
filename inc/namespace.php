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
