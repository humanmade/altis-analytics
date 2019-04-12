<?php
/**
 * Analytics Module.
 *
 * @package hm-platform/analytics
 */

namespace HM\Platform\Analytics;

use function HM\Platform\register_module;

require_once __DIR__ . '/inc/namespace.php';

add_action( 'hm-platform.modules.init', function () {
	$default_settings = [
		'enabled' => true,
		'google-tag-manager' => [
			'network' => '',
			'sites' => [],
		],
	];
	register_module( 'analytics', __DIR__, 'Analytics', $default_settings, __NAMESPACE__ . '\\bootstrap' );
} );
