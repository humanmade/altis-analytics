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
			'dashboard' => true,
			'experiments' => [
				'titles' => true,
			],
			'data-retention-days' => 90,
		],
		'google-tag-manager' => [
			'network' => '',
			'sites' => [],
			'event-tracking' => false,
		],
		'integrations' => [
			'segment' => [
				'enabled' => true,
				'api-key' => '',
			],
		],
	];
	$options = [
		'defaults' => $default_settings,
	];
	register_module( 'analytics', __DIR__, 'Analytics', $options, __NAMESPACE__ . '\\bootstrap' );
} );
