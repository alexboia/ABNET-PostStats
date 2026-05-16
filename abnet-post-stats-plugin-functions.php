<?php
declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

function abnet_post_stats_run(): void {
	abnet_post_stats()->run();
}

function abnet_post_stats():ABNet_PostStats {
	return ABNet_PostStats::getInstance();
}