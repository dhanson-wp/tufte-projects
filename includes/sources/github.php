<?php
/**
 * GitHub source adapter.
 *
 * Reads the repository (default branch, license), the WordPress file header
 * on the default branch (version and requirements), and the latest release
 * (date and download asset). Version always comes from the header, never
 * the release tag: releases lag behind the code on Derek's repos.
 *
 * @package Tufte_Projects
 * @since 1.5.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extract owner and repo from a GitHub URL.
 *
 * @param string $url Repository URL.
 * @return array{0:string,1:string}|null
 */
function tufte_projects_parse_github_url( string $url ): ?array {
	$parts = wp_parse_url( trim( $url ) );
	if ( empty( $parts['host'] ) || ! in_array( strtolower( $parts['host'] ), array( 'github.com', 'www.github.com' ), true ) ) {
		return null;
	}
	$segments = array_values( array_filter( explode( '/', $parts['path'] ?? '' ) ) );
	if ( count( $segments ) < 2 ) {
		return null;
	}
	$repo = preg_replace( '/\.git$/', '', $segments[1] );
	return array( $segments[0], $repo );
}

/**
 * GET a URL with the shared timeout and user agent. Returns the body or WP_Error.
 *
 * @param string $url     URL.
 * @param array  $headers Extra headers.
 * @return string|WP_Error
 */
function tufte_projects_http_get( string $url, array $headers = array() ) {
	$response = wp_remote_get(
		$url,
		array(
			'timeout'    => 10,
			'user-agent' => tufte_projects_user_agent(),
			'headers'    => $headers,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error( 'tufte_http_' . $code, sprintf( 'HTTP %d from %s', $code, $url ), array( 'status' => $code ) );
	}
	return wp_remote_retrieve_body( $response );
}

/**
 * Fetch everything the GitHub adapter needs and map it.
 *
 * Up to four requests: repo, raw style.css, raw {repo}.php (only when
 * style.css is not a theme header), releases/latest. A missing release is
 * not an error; a missing repo or header is.
 *
 * Only a 404 means "no releases". Any other failed release request (a rate
 * limit, a 5xx, a timeout) keeps the release fields from $previous, so a bad
 * sync never blanks a good release date or download link.
 *
 * @param string     $url      Repository URL.
 * @param array|null $previous Cached GitHub values from the last good sync.
 * @return array|WP_Error
 */
function tufte_projects_fetch_github( string $url, ?array $previous = null ) {
	$parsed = tufte_projects_parse_github_url( $url );
	if ( null === $parsed ) {
		return new WP_Error( 'tufte_github_url', sprintf( 'Not a GitHub repository URL: %s', $url ) );
	}
	list( $owner, $repo ) = $parsed;
	$api                  = 'https://api.github.com/repos/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo );
	$accept               = array( 'Accept' => 'application/vnd.github+json' );

	$repo_body = tufte_projects_http_get( $api, $accept );
	if ( is_wp_error( $repo_body ) ) {
		return $repo_body;
	}
	$repo_data = json_decode( $repo_body, true );
	if ( ! is_array( $repo_data ) || empty( $repo_data['default_branch'] ) ) {
		return new WP_Error( 'tufte_github_repo', sprintf( 'No repository data for %s/%s', $owner, $repo ) );
	}

	$raw_base = 'https://raw.githubusercontent.com/' . rawurlencode( $owner ) . '/' . rawurlencode( $repo ) . '/' . rawurlencode( (string) $repo_data['default_branch'] ) . '/';
	$header   = array();
	$style    = tufte_projects_http_get( $raw_base . 'style.css' );
	if ( ! is_wp_error( $style ) ) {
		$header = tufte_projects_parse_file_header( $style );
	}
	if ( empty( $header['version'] ) ) {
		$main = tufte_projects_http_get( $raw_base . rawurlencode( $repo ) . '.php' );
		if ( ! is_wp_error( $main ) ) {
			$header = tufte_projects_parse_file_header( $main );
		}
	}
	if ( empty( $header['version'] ) ) {
		return new WP_Error( 'tufte_github_header', sprintf( 'No WordPress file header with a Version found in %s/%s', $owner, $repo ) );
	}

	$release      = array();
	$release_body = tufte_projects_http_get( $api . '/releases/latest', $accept );
	if ( ! is_wp_error( $release_body ) ) {
		$decoded = json_decode( $release_body, true );
		$release = is_array( $decoded ) ? $decoded : array();
	}

	$mapped = tufte_projects_map_github( $repo_data, $header, $release );

	$error_data  = is_wp_error( $release_body ) ? $release_body->get_error_data() : null;
	$no_releases = is_array( $error_data ) && 404 === ( $error_data['status'] ?? null );
	if ( is_wp_error( $release_body ) && ! $no_releases ) {
		error_log( sprintf( '[tufte-projects] %s/%s release fetch failed, keeping cached release: %s', $owner, $repo, $release_body->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		foreach ( array( 'release_date', 'link_download' ) as $key ) {
			$mapped[ $key ] = isset( $previous[ $key ] ) ? (string) $previous[ $key ] : '';
		}
	}

	return $mapped;
}

/**
 * Map GitHub data to registry field values. Pure.
 *
 * @param array $repo    Decoded /repos/{owner}/{repo} response.
 * @param array $header  Output of tufte_projects_parse_file_header().
 * @param array $release Decoded /releases/latest response, or empty array.
 * @return array<string,string>
 */
function tufte_projects_map_github( array $repo, array $header, array $release ): array {
	$h = static fn( string $key ): string => isset( $header[ $key ] ) ? trim( (string) $header[ $key ] ) : '';

	$license = $h( 'license' );
	if ( '' === $license ) {
		$spdx    = isset( $repo['license']['spdx_id'] ) ? (string) $repo['license']['spdx_id'] : '';
		$license = ( '' !== $spdx && 'NOASSERTION' !== $spdx ) ? $spdx : '';
	}

	$download = '';
	if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
		foreach ( $release['assets'] as $asset ) {
			if ( ! empty( $asset['browser_download_url'] ) ) {
				$download = (string) $asset['browser_download_url'];
				break;
			}
		}
	}

	$html_url = isset( $repo['html_url'] ) ? rtrim( (string) $repo['html_url'], '/' ) : '';

	return array(
		'version'       => $h( 'version' ),
		'release_date'  => isset( $release['published_at'] ) ? (string) $release['published_at'] : '',
		'requires_wp'   => $h( 'requires_wp' ),
		'tested_up_to'  => $h( 'tested_up_to' ),
		'requires_php'  => $h( 'requires_php' ),
		'license'       => $license,
		'link_download' => $download,
		'link_support'  => $html_url ? $html_url . '/issues' : '',
	);
}
