<?php
/**
 * Block templates for the Projects archive and single views.
 *
 * Registered with register_block_template() so any block theme gets them
 * without shipping its own. A theme file or a template saved in the Site
 * Editor with the same slug still wins.
 *
 * @package Tufte_Projects
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the plugin's block templates.
 *
 * @return void
 */
function tufte_projects_register_templates(): void {
	if ( ! function_exists( 'register_block_template' ) ) {
		return;
	}

	$templates = array(
		'single-project'  => __( 'Single Project', 'tufte-projects' ),
		'archive-project' => __( 'Projects Archive', 'tufte-projects' ),
	);

	foreach ( $templates as $slug => $title ) {
		$file = plugin_dir_path( TUFTE_PROJECTS_PLUGIN_FILE ) . 'templates/' . $slug . '.html';
		if ( ! is_readable( $file ) ) {
			continue;
		}
		register_block_template(
			'tufte-projects//' . $slug,
			array(
				'title'   => $title,
				'content' => (string) file_get_contents( $file ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			)
		);
	}
}
add_action( 'init', 'tufte_projects_register_templates' );
