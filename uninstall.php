<?php
/**
 * Uninstall: clear the scheduled sync. Content is never deleted.
 *
 * @package Tufte_Projects
 */

declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

wp_clear_scheduled_hook( 'tufte_blocks_projects_sync' );
