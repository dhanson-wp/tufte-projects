<?php
// Plugin wiring: registration and scheduling happen when the plugin loads.

tufte_projects_assert_same( true, post_type_exists( 'project' ), 'project post type registered' );
tufte_projects_assert_same( true, taxonomy_exists( 'project_type' ), 'project_type taxonomy registered' );
tufte_projects_assert_same( true, taxonomy_exists( 'project_tool' ), 'project_tool taxonomy registered' );
tufte_projects_assert_same( true, null !== get_block_bindings_source( 'tufte-blocks/project-field' ), 'bindings source registered' );

tufte_projects_assert_same( true, false !== wp_next_scheduled( 'tufte_blocks_projects_sync' ), 'sync event is scheduled' );
$tufte_projects_events = 0;
foreach ( _get_cron_array() as $tufte_projects_hooks ) {
	if ( isset( $tufte_projects_hooks['tufte_blocks_projects_sync'] ) ) {
		$tufte_projects_events += count( $tufte_projects_hooks['tufte_blocks_projects_sync'] );
	}
}
tufte_projects_assert_same( 1, $tufte_projects_events, 'exactly one sync event' );
tufte_projects_assert_same( true, false !== has_action( 'tufte_blocks_projects_sync', 'tufte_projects_sync_all' ), 'sync job is hooked' );

$tufte_projects_registry = WP_Block_Templates_Registry::get_instance();
tufte_projects_assert_same( true, $tufte_projects_registry->is_registered( 'tufte-projects//single-project' ), 'single-project template registered by the plugin' );
tufte_projects_assert_same( true, $tufte_projects_registry->is_registered( 'tufte-projects//archive-project' ), 'archive-project template registered by the plugin' );

$tufte_projects_patterns = WP_Block_Patterns_Registry::get_instance();
foreach ( array( 'project-card', 'project-faq', 'project-features', 'project-install-steps', 'syllabus' ) as $tufte_projects_name ) {
	tufte_projects_assert_same( true, $tufte_projects_patterns->is_registered( 'tufte-projects/' . $tufte_projects_name ), "pattern tufte-projects/{$tufte_projects_name} registered" );
}
tufte_projects_assert_same( true, defined( 'TUFTE_PROJECTS_VERSION' ), 'version constant defined' );
