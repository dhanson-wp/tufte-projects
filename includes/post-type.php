<?php
/**
 * Project post type and taxonomies.
 *
 * Moved verbatim from functions.php in 1.5.0. No behavior change.
 *
 * @package Tufte_Projects
 * @since 1.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Projects custom post type.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_projects_register_project_post_type(): void {
	register_post_type(
		'project',
		array(
			'labels'        => array(
				'name'                  => __( 'Projects', 'tufte-projects' ),
				'singular_name'         => __( 'Project', 'tufte-projects' ),
				'add_new_item'          => __( 'Add New Project', 'tufte-projects' ),
				'edit_item'             => __( 'Edit Project', 'tufte-projects' ),
				'new_item'              => __( 'New Project', 'tufte-projects' ),
				'view_item'             => __( 'View Project', 'tufte-projects' ),
				'view_items'            => __( 'View Projects', 'tufte-projects' ),
				'search_items'          => __( 'Search Projects', 'tufte-projects' ),
				'not_found'             => __( 'No projects found.', 'tufte-projects' ),
				'not_found_in_trash'    => __( 'No projects found in Trash.', 'tufte-projects' ),
				'all_items'             => __( 'All Projects', 'tufte-projects' ),
				'archives'              => __( 'Project Archives', 'tufte-projects' ),
				'attributes'            => __( 'Project Attributes', 'tufte-projects' ),
				'item_published'        => __( 'Project published.', 'tufte-projects' ),
				'item_updated'          => __( 'Project updated.', 'tufte-projects' ),
			),
			'public'        => true,
			'has_archive'   => 'projects',
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
			'rewrite'       => array(
				'slug'       => 'projects',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'tufte_projects_register_project_post_type' );

/**
 * Register project taxonomies.
 *
 * @since 1.3.0
 * @return void
 */
function tufte_projects_register_project_taxonomies(): void {
	register_taxonomy(
		'project_type',
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Project Types', 'tufte-projects' ),
				'singular_name' => __( 'Project Type', 'tufte-projects' ),
				'add_new_item'  => __( 'Add New Project Type', 'tufte-projects' ),
				'edit_item'     => __( 'Edit Project Type', 'tufte-projects' ),
				'search_items'  => __( 'Search Project Types', 'tufte-projects' ),
				'all_items'     => __( 'All Project Types', 'tufte-projects' ),
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => 'project-type',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'project_tool',
		'project',
		array(
			'labels'       => array(
				'name'          => __( 'Tools', 'tufte-projects' ),
				'singular_name' => __( 'Tool', 'tufte-projects' ),
				'add_new_item'  => __( 'Add New Tool', 'tufte-projects' ),
				'edit_item'     => __( 'Edit Tool', 'tufte-projects' ),
				'search_items'  => __( 'Search Tools', 'tufte-projects' ),
				'all_items'     => __( 'All Tools', 'tufte-projects' ),
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => true,
			'rewrite'      => array(
				'slug'       => 'project-tool',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'tufte_projects_register_project_taxonomies' );

/**
 * Register the types and flush rewrite rules when the plugin is activated.
 *
 * @return void
 */
function tufte_projects_activate(): void {
	tufte_projects_register_project_post_type();
	tufte_projects_register_project_taxonomies();
	flush_rewrite_rules();
}
register_activation_hook( TUFTE_PROJECTS_PLUGIN_FILE, 'tufte_projects_activate' );
