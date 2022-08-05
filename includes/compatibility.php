<?php

/**
 * Check if certain plugins or themes are installed and activated
 * and if found dynamically load the relevant /includes/compatibility/ files.
 *
 * @since 1.0
 */
function rwstripe_compatibility_checker() {
	$compat_checks = [
		[
			'file'        => 'elementor.php',
			'check_type'  => 'constant',
			'check_value' => 'ELEMENTOR_VERSION',
		],
	];

	foreach ( $compat_checks as $value ) {
		if ( rwstripe_compatibility_checker_is_requirement_met( $value ) ) {
			include_once( RWSTRIPE_DIR . '/includes/compatibility/' . $value['file'] ) ;
		}
	}
}
add_action( 'plugins_loaded', 'rwstripe_compatibility_checker' );

/**
 * Check whether the requirement is met.
 *
 * @since 1.0
 *
 * @param array $requirement The requirement config (check_type, check_value, check_constant_true).
 *
 * @return bool Whether the requirement is met.
 */
function rwstripe_compatibility_checker_is_requirement_met( $requirement ) {
	// Make sure we have the keys that we expect.
	if ( ! isset( $requirement['check_type'], $requirement['check_value'] ) ) {
		return false;
	}

	// Check for a constant and maybe check if the constant is true-ish.
	if ( 'constant' === $requirement['check_type'] ) {
		return (
			defined( $requirement['check_value'] )
			&& (
				empty( $requirement['check_constant_true'] )
				|| constant( $requirement['check_value'] )
			)
		);
	}

	// Check for a function.
	if ( 'function' === $requirement['check_type'] ) {
		return function_exists( $requirement['check_value'] );
	}

	// Check for a class.
	if ( 'class' === $requirement['check_type'] ) {
		return class_exists( $requirement['check_value'] );
	}

	return false;
}
