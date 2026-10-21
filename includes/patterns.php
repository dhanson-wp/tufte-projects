<?php
/**
 * Block patterns for project pages.
 *
 * Each file in patterns/ is a PHP file with a header block, the same format
 * a theme uses. The file is included with output buffering to get its markup.
 *
 * @package Tufte_Projects
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the pattern category and the bundled patterns.
 *
 * @return void
 */
function tufte_projects_register_patterns(): void {
	register_block_pattern_category(
		'tufte-projects',
		array( 'label' => __( 'Projects', 'tufte-projects' ) )
	);

	$names = array( 'project-card', 'project-faq', 'project-features', 'project-install-steps', 'syllabus' );

	foreach ( $names as $name ) {
		$file = plugin_dir_path( TUFTE_PROJECTS_PLUGIN_FILE ) . 'patterns/' . $name . '.php';
		if ( ! is_readable( $file ) ) {
			continue;
		}

		$data = get_file_data(
			$file,
			array(
				'title'       => 'Title',
				'slug'        => 'Slug',
				'description' => 'Description',
				'keywords'    => 'Keywords',
				'blockTypes'  => 'Block Types',
				'postTypes'   => 'Post Types',
			)
		);

		ob_start();
		include $file;
		$content = (string) ob_get_clean();

		$pattern = array(
			'title'      => $data['title'],
			'content'    => $content,
			'categories' => array( 'tufte-projects' ),
		);
		foreach ( array( 'description' ) as $key ) {
			if ( '' !== $data[ $key ] ) {
				$pattern[ $key ] = $data[ $key ];
			}
		}
		foreach ( array( 'keywords', 'blockTypes', 'postTypes' ) as $key ) {
			if ( '' !== $data[ $key ] ) {
				$pattern[ $key ] = array_map( 'trim', explode( ',', $data[ $key ] ) );
			}
		}

		register_block_pattern( $data['slug'], $pattern );
	}
}
add_action( 'init', 'tufte_projects_register_patterns' );
