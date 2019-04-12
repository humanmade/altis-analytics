<?php
/**
 * Analytics Module functions.
 *
 * @package hm-platform/analytics
 */

namespace HM\Platform\Analytics;

use const HM\Platform\ROOT_DIR;
use HM\Platform\Module;

function bootstrap( Module $module ) {
	$settings = $module->get_settings();

	if ( $settings['google-tag-manager'] ) {
		add_action( 'muplugins_loaded', __NAMESPACE__ . '\\load_gtm' );
		configure_gtm( $settings['google-tag-manager'] );
	}
}

function load_gtm() {
	require_once ROOT_DIR . '/vendor/humanmade/hm-gtm/hm-gtm.php';
}

/**
 * Filter the tag manager container IDs.
 *
 * @param array $settings
 * @return void
 */
function configure_gtm( array $settings ) {
	add_filter( 'hm_gtm_id', function ( string $id ) use ( $settings ) : string {
		if ( ! empty( $settings['sites'] ) && is_array( $settings['sites'] ) ) {
			$id = $settings['sites'][ $_SERVER['HTTP_HOST'] ] ?? $id;
		}

		return $id;
	} );

	add_filter( 'hm_gtm_network_id', function ( string $id ) use ( $settings ) : string {
		return $settings['network'] ?? $id;
	} );
}
