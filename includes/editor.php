<?php
/**
 * "Project details" sidebar panel: enqueue and config.
 *
 * Loads only when editing a project. The JS is vanilla wp.element (no JSX,
 * no build) and reads the field registry from window.tufteProjectFields.
 *
 * @package Tufte_Projects
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue the panel script on project edit screens.
 *
 * @return void
 */
function tufte_projects_enqueue_editor(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'project' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}

	wp_enqueue_script(
		'tufte-projects-fields',
		plugins_url( 'assets/js/project-fields.js', TUFTE_PROJECTS_PLUGIN_FILE ),
		array( 'wp-plugins', 'wp-editor', 'wp-components', 'wp-element', 'wp-data', 'wp-core-data', 'wp-api-fetch', 'wp-i18n' ),
		TUFTE_PROJECTS_VERSION,
		true
	);

	$fields = array();
	foreach ( tufte_projects_fields() as $key => $field ) {
		$fields[] = array(
			'key'     => $key,
			'metaKey' => tufte_projects_meta_key( $key ),
			'label'   => $field['label'],
			'type'    => $field['type'],
			'group'   => $field['group'],
			'manual'  => $field['manual'],
		);
	}

	wp_add_inline_script(
		'tufte-projects-fields',
		'window.tufteProjectFields = ' . wp_json_encode(
			array(
				'fields'   => $fields,
				'restBase' => 'tufte-projects/v1/projects',
			)
		) . ';',
		'before'
	);
}
add_action( 'enqueue_block_editor_assets', 'tufte_projects_enqueue_editor' );
