<?php
/**
 * Analytics Module.
 *
 * @package altis/analytics
 */

namespace Altis\Analytics; // @codingStandardsIgnoreLine

use function Altis\register_module;

add_action( 'altis.modules.init', function () {
	$default_settings = [
		'enabled' => true,
		'native' => [
			'experiments' => [
				'titles' => true,
			],
			'data-retention-days' => 14,
		],
		'google-tag-manager' => [
			'network' => '',
			'sites' => [],
			'event-tracking' => false,
		],
	];
	register_module( 'analytics', __DIR__, 'Analytics', $default_settings, __NAMESPACE__ . '\\bootstrap' );
} );
