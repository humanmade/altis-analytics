# Custom A/B Test Example

Step 1: Register the test, and the custom meta field that will control the value we'll be testing.

```php
<?php
// functions.php

// Register the test.
add_action( 'init', function() {
	register_post_ab_test( 'cta_button_style', [
		'label' => __( 'CTA Button styles', 'my-theme' ),
		'singular_label' => __( 'CTA Button style', 'my-theme' ),
		'rest_api_variants_type' => 'string',
		'goal' => 'click',
		'variant_callback' => function ( $variant_value, $post_id ) {
			$label = __( 'Register now', 'my-theme' );
			return sprintf(
				'<button class="cta-button cta-button-%s">%s</button>',
				$variant_value,
				$label
			);
		},
		'post_types' => [ 'event' ],
		'editor_scripts' => [
			get_theme_file_uri( 'assets/js/feature/editor.js' ) => [
				'wp-i18n',
			],
		],
	] );
} );

// Register the meta field.
add_action( 'init', function() {
	register_post_meta( 'event', 'cta_button_style', [
		'show_in_rest' => true,
	] );
} );
```

Step 2: Use a select input to choose from different variations of the button styles.

```js
// editor.js

import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Custom input component
 *
 * @param {React.ComponentProps} props The component props.
 * @returns {React.ReactNode} Image input field component.
 */
const CustomInput = props => {
	const {
		value,
		onChange,
		isEditable,
	} = props;

	return (
		<div>
			{ ( isEditable ) && (
				<SelectControl
					label={ null }
					value={ value }
					options={ [
						{ label: __( 'Big', 'my-theme' ), value: 'big' },
						{ label: __( 'Medium', 'my-theme' ), value: 'medium' },
						{ label: __( 'Small', 'my-theme' ), value: 'small' },
					] }
					onChange={ onChange }
				/>
			) }
			{ value && (
				{ value }
			) }
		</div>
	);
};

// Filter the test options to use a custom input, custom default value, and custom revert value callback.
wp.hooks.addFilter( 'altis.experiments.sidebar.test.cta_button_style', 'altis.experiments.features.cta_button_style', options => ( {
	...options,
	component: CustomInput,

	/**
	 * Add/replace dispatchers available to the experiment panel.
	 *
	 * @param {Function} dispatch Dispatch function.
	 * @returns {object} Object with dispatcher callbacks.
	 */
	dispatcher: dispatch => ( {
		/**
		 * Callback to revert to the default value.
		 *
		 * @param {*} value Value to revert to.
		 */
		revertValue: value => dispatch( 'core/editor' ).editPost( { cta_button_style: value } ),
	} ),

	/**
	 * Add/replace selectors available to the experiment panel.
	 *
	 * @param {Function} select Select function.
	 * @returns {object} Object with selector callbacks.
	 */
	selector: select => ( {
		defaultValue: 'medium',
	} ),
} ) );
```

Step 3: Output the test output in your template.

```php
<?php
// template-parts/cta.php

use Altis\Analytics\Experiments;
 
$default = sprintf(
	'<button class="cta-button cta-button-medium">%s</button>',
	__( 'Register Now', 'my-theme' )
);

?>
<div class="cta-container">
	<?php
		echo Experiments\output_ab_test_html_for_post(
			'cta_button_style',
			get_the_ID(),
			$default
		);
	?>
</div>
```

