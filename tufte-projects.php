<?php
/**
 * Plugin Name:       Tufte Projects
 * Description:       A Projects post type for showcasing plugins, themes, and tools, with fields synced from WordPress.org and GitHub.
 * Version:           0.1.1
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Derek Hanson
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tufte-projects
 *
 * @package Tufte_Projects
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TUFTE_PROJECTS_VERSION', '0.1.1' );
define( 'TUFTE_PROJECTS_PLUGIN_FILE', __FILE__ );

/**
 * Files are loaded in dependency order. The registry (fields.php) must load
 * before anything that reads it.
 */
foreach ( array(
	'post-type',
	'tool-icons',
	'fields',
	'header-parser',
	'sources/wporg',
	'sources/github',
	'sync',
	'bindings',
	'editor',
	'templates',
	'patterns',
) as $tufte_projects_file ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/' . $tufte_projects_file . '.php';
}
unset( $tufte_projects_file );
