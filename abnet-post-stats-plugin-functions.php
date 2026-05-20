<?php
/**
 * @package ABNet_PostStats
 * @since 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

function abnet_post_stats_run(): void {
	abnet_post_stats()->run();
}

/**
 * @access private
 * @return ABNet_PostStats 
 */
function abnet_post_stats(): ABNet_PostStats {
	return ABNet_PostStats::getInstance();
}

function abnet_posts_stats_api(): ABNet_PostStats_PublicApi {
	static $api = null;
	if ($api === null) {
		$api = new ABNet_PostStats_PublicApi();
	}
	return $api;
}