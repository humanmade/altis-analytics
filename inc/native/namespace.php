<?php
/**
 * Native analytics integrations.
 */

namespace Altis\Analytics\Native;

use const Altis\ROOT_DIR;
use function Altis\Enhanced_Search\get_elasticsearch_url;
use function Altis\Experiments\get_ab_test_variants_for_post;
use function Altis\Experiments\get_post_ab_test;
use function Altis\get_config;
use function HM\Workflows\get_post_assignees;
use HM\Workflows\Event;
use HM\Workflows\Workflow;

/**
 * Setup integration.
 */
function bootstrap() {
	$config = get_config()['modules']['analytics']['native'];

	// Set Analytics plugin to use core Elasticsearch instance.
	add_filter( 'altis.analytics.elasticsearch.url', function () {
		return get_elasticsearch_url();
	} );

	// Set data retention period.
	add_filter( 'altis.analytics.max_index_age', __NAMESPACE__ . '\\set_data_retention_days' );

	// Load Experiments.
	if ( $config['experiments'] ) {
		load_experiments();

		// Enable/Disable Experiments Features.
		if ( is_array( $config['experiments'] ) ) {
			foreach ( $config['experiments'] as $feature => $enabled ) {
				add_filter(
					"altis.experiments.features.{$feature}",
					$enabled ? '__return_true' : '__return_false'
				);
			}
		}

		// Experiments notifications.
		if ( get_config()['modules']['workflow']['enabled'] ) {
			add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup_notifications', 12 );
		}
	}

	// Add endpoint data from cloudfront headers.
	add_filter( 'altis.analytics.data.endpoint', __NAMESPACE__ . '\\add_endpoint_data' );
}

/**
 * Set the number of days to keep data for.
 *
 * @param int $days The number of days to keep analytics data for.
 * @return int
 */
function set_data_retention_days( int $days ) : int {
	if ( get_config()['modules']['analytics']['native']['data-retention-days'] ?? false ) {
		return get_config()['modules']['analytics']['native']['data-retention-days'];
	}
	return $days;
}

/**
 * Load Experiments plugin.
 */
function load_experiments() {
	require_once ROOT_DIR . '/vendor/altis/experiments/plugin.php';
}

/**
 * Hook into the workflow module for experiment notifications.
 */
function setup_notifications() {
	// Double check the Workflows plugin is loaded.
	if ( ! class_exists( 'HM\\Workflows\\Event' ) ) {
		return;
	}

	// Remove default notifications.
	remove_action( 'altis.experiments.test.ended', 'Altis\\Experiments\\send_post_ab_test_notification', 10, 2 );

	// Experiment events.
	$events = [
		'altis.experiments.test.ended' => __( 'An experiment has ended', 'altis-analytics' ),
	];

	foreach ( $events as $event => $label ) {
		// Create the event handler.
		$event = Event::register( $event );

		$event->set_listener( [
			'action' => $event->get_id(),
			'accepted_args' => 2,
		] );

		$event->add_recipient_handler( 'post_author', function ( string $test_id, int $post_id ) {
			$post = get_post( $post_id );
			return $post->post_author;
		}, __( 'Post author', 'altis-analytics' ) );

		$event->add_recipient_handler( 'post_assignees', function ( string $test_id, int $post_id ) {
			return get_post_assignees( $post_id );
		}, __( 'Post assignees', 'altis-analytics' ) );

		$event->add_message_tags( [
			'test.title' => function ( string $test_id ) {
				$test = get_post_ab_test( $test_id );
				return $test['label'];
			},
			'post.title' => function ( string $test_id, int $post_id ) {
				$post = get_post( $post_id );
				$variants = get_ab_test_variants_for_post( $test_id, $post_id );
				return count( $variants ) ? $variants[0] : $post->post_title;
			},
		] );

		$event->add_message_action(
			'view',
			__( 'View results', 'altis-analytics' ),
			function ( string $test_id, int $post_id ) {
				return get_edit_post_link( $post_id, 'db' ) . '#experiments-' . $test_id;
			},
			function ( string $test_id, int $post_id ) : array {
				return [
					'test_id' => $test_id,
					'post_id' => $post_id,
				];
			},
			[
				'test_id' => 'sanitize_text_field',
				'post_id' => 'intval',
			]
		);

		Workflow::register( $event->get_id() )
			->when( $event )
			->who( [
				'post_author',
				'post_assignees',
				'editor',
				'administrator',
			] )
			->what(
				__( 'Your test %test.title% on "%post.title%" has ended', 'altis-analytics' )
			)
			->where( 'email' )
			->where( 'dashboard' );
	}
}

/**
 * Add additional endpoint data based on CloudFront headers.
 *
 * @param array $data
 * @return array
 */
function add_endpoint_data( array $data ) : array {

	// Add viewer country.
	if ( isset( $_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY'] ) ) {
		if ( ! isset( $data['Location'] ) ) {
			$data['Location'] = [];
		}
		$data['Location']['Country'] = strtoupper( sanitize_key( $_SERVER['HTTP_CLOUDFRONT_VIEWER_COUNTRY'] ) );
	}

	return $data;
}
