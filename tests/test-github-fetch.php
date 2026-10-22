<?php
declare(strict_types=1);

// Serve the repo and style.css from fixtures, and /releases/latest from
// whatever the case puts in $GLOBALS['tufte_projects_test_release'].
$tufte_projects_fake_http = static function ( $pre, $args, $url ) {
	$reply = static fn( int $code, string $body, array $headers = array() ): array => array(
		'headers'  => $headers,
		'body'     => $body,
		'response' => array( 'code' => $code, 'message' => '' ),
		'cookies'  => array(),
		'filename' => null,
	);
	if ( 'https://api.github.com/repos/dhanson-wp/tufte-blocks' === $url ) {
		return $reply( 200, tufte_projects_fixture( 'github-repo-tufte-blocks.json' ) );
	}
	if ( str_ends_with( $url, '/style.css' ) ) {
		return $reply( 200, tufte_projects_fixture( 'header-style-css.txt' ) );
	}
	if ( str_ends_with( $url, '/releases/latest' ) ) {
		$release = $GLOBALS['tufte_projects_test_release'];
		return is_wp_error( $release ) ? $release : $reply( ...$release );
	}
	return new WP_Error( 'tufte_test_unexpected', 'Unexpected request: ' . $url );
};
add_filter( 'pre_http_request', $tufte_projects_fake_http, 10, 3 );

$repo_url = 'https://github.com/dhanson-wp/tufte-blocks';
$previous = array(
	'version'       => '1.4.3',
	'release_date'  => '2026-02-27T18:49:41Z',
	'link_download' => 'https://github.com/dhanson-wp/tufte-blocks/releases/download/v1.3.0/tufte-blocks-1.3.0.zip',
);

$GLOBALS['tufte_projects_test_release'] = array( 403, '{"message":"API rate limit exceeded"}', array( 'x-ratelimit-remaining' => '0' ) );
$limited                                = tufte_projects_fetch_github( $repo_url, $previous );
tufte_projects_assert_same( '1.4.4', $limited['version'], 'github fetch: a rate-limited release still refreshes the header fields' );
tufte_projects_assert_same( $previous['release_date'], $limited['release_date'], 'github fetch: a rate-limited release keeps the cached release_date' );
tufte_projects_assert_same( $previous['link_download'], $limited['link_download'], 'github fetch: a rate-limited release keeps the cached link_download' );

$GLOBALS['tufte_projects_test_release'] = array( 502, 'Bad gateway' );
$bad_gateway                            = tufte_projects_fetch_github( $repo_url, $previous );
tufte_projects_assert_same( $previous['release_date'], $bad_gateway['release_date'], 'github fetch: a 5xx release keeps the cached release_date' );

$GLOBALS['tufte_projects_test_release'] = new WP_Error( 'http_request_failed', 'cURL error 28: Operation timed out' );
$timed_out                              = tufte_projects_fetch_github( $repo_url, $previous );
tufte_projects_assert_same( $previous['link_download'], $timed_out['link_download'], 'github fetch: a timed-out release keeps the cached link_download' );

$GLOBALS['tufte_projects_test_release'] = array( 404, '{"message":"Not Found"}' );
$gone                                   = tufte_projects_fetch_github( $repo_url, $previous );
tufte_projects_assert_same( '', $gone['release_date'], 'github fetch: a 404 release clears release_date' );
tufte_projects_assert_same( '', $gone['link_download'], 'github fetch: a 404 release clears link_download' );

$GLOBALS['tufte_projects_test_release'] = array( 403, '{"message":"API rate limit exceeded"}' );
$first_sync                             = tufte_projects_fetch_github( $repo_url, null );
tufte_projects_assert_same( '', $first_sync['release_date'], 'github fetch: an error with nothing cached leaves release_date empty' );

$GLOBALS['tufte_projects_test_release'] = array( 200, tufte_projects_fixture( 'github-release-tufte-blocks.json' ) );
$fresh                                  = tufte_projects_fetch_github( $repo_url, array( 'release_date' => 'stale', 'link_download' => 'stale' ) );
tufte_projects_assert_same( '2026-02-27T18:49:41Z', $fresh['release_date'], 'github fetch: a good release replaces the cached release_date' );

remove_filter( 'pre_http_request', $tufte_projects_fake_http, 10 );
unset( $GLOBALS['tufte_projects_test_release'] );
