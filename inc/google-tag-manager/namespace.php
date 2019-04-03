<?php
/**
 * Google Tag Manager configuration.
 *
 * @package hm-platform/analytics
 */

namespace HM\Platform\Analytics\Google_Tag_Manager;

use function HM\Platform\get_config as get_platform_config;
use HM_GTM\Plugin;

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

function get_config() : array {
	return get_platform_config()['modules']['analytics']['google-tag-manager'];
}

function filter_network_container_id( string $id ) : string {
	return get_config()['network'] ?? $id;
}

function filter_site_container_id( string $id ) : string {
	return get_config()['sites'][ $_SERVER['HTTP_HOST'] ] ?? $id;
}
