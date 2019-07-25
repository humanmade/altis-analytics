<?php
/**
 * Analytics Module functions.
 *
 * @package altis/analytics
 */

namespace Altis\Analytics;

use Altis\Module;
use const Altis\ROOT_DIR;
use function Altis\Enhanced_Search\get_elasticsearch_url;

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

	// Set Analytics plugin to use core instance.
	add_filter( 'altis.analytics.elasticsearch.url', function () {
		return get_elasticsearch_url();
	} );
}

/**
 * Loads Google Tag Manager plugin and related code.
 */
function load_google_tag_manager() {
	require_once ROOT_DIR . '/vendor/humanmade/hm-gtm/plugin.php';
	require_once __DIR__ . '/google_tag_manager/namespace.php';
	Google_Tag_Manager\bootstrap();
}
