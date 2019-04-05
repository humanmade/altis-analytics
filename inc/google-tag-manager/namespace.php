<?php
/**
 * Google Tag Manager configuration.
 *
 * @package hm-platform/analytics
 */

namespace HM\Platform\Analytics\Google_Tag_Manager;

use function HM\Platform\get_config as get_platform_config;
use HM_GTM\Plugin;

/**
 * Set up Google Tag Manager plugin filters based on config.
 */
function bootstrap() {
	$config = get_config();

	$plugin = Plugin::get_instance();

	// Set network container ID and remove admin.
	if ( $config['network'] ) {
		add_filter( 'hm_gtm_network_id', __NAMESPACE__ . '\\filter_network_container_id' );
		remove_action( 'wpmu_options', [ $plugin, 'show_network_settings' ] );
	}

	// Set site container ID and remove admin.
	if ( $config['sites'][ $_SERVER['HTTP_HOST'] ] ?? false ) {
		add_filter( 'hm_gtm_id', __NAMESPACE__ . '\\filter_site_container_id' );
		remove_action( 'admin_init', [ $plugin, 'action_admin_init' ] );
	}
}

/**
 * Gets the tag manager related part of the full platform config.
 *
 * @return array
 */
function get_config() : array {
	return get_platform_config()['modules']['analytics']['google-tag-manager'];
}

/**
 * Filters the network container ID from the config.
 * Falls back to the database value.
 *
 * @param string $id The default or database value for the network container ID.
 * @return string
 */
function filter_network_container_id( string $id ) : string {
	return get_config()['network'] ?? $id;
}

/**
 * Filters the site container ID from the config by host name.
 * Falls back to the database value.
 *
 * @param string $id The default or database value for the site container ID.
 * @return string
 */
function filter_site_container_id( string $id ) : string {
	return get_config()['sites'][ $_SERVER['HTTP_HOST'] ] ?? $id;
}
