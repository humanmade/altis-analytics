<?php
/**
 * Analytics Module.
 *
 * @package altis/analytics
 */

namespace Altis\Analytics; // @codingStandardsIgnoreLine

use function Altis\register_module;

require_once __DIR__ . '/inc/namespace.php';

add_action( 'altis.modules.init', function () {
	$default_settings = [
		'enabled' => true,
		'native' => [
			'experiments' => [
				'titles' => true,
			],
		],
		'google-tag-manager' => [
			'network' => '',
			'sites' => [],
			'event-tracking' => false,
		],
	];
	register_module( 'analytics', __DIR__, 'Analytics', $default_settings, __NAMESPACE__ . '\\bootstrap' );
} );
