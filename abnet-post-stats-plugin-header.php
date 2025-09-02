<?php
declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('ABNET_POST_STATS_VERSION', '1.1.0');
define('ABNET_POST_STATS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ABNET_POST_STATS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ABNET_POST_STATS_PLUGIN_FILE', __FILE__);

define('ABNET_POST_STATS_INC_DIR', dirname(ABNET_POST_STATS_PLUGIN_FILE) . '/includes/');
define('ABNET_POST_STATS_VIEWS_DIR', dirname(ABNET_POST_STATS_PLUGIN_FILE) . '/views/');
define('ABNET_POST_STATS_DATA_DIR', dirname(ABNET_POST_STATS_PLUGIN_FILE) . '/data/');

define('ABNET_POST_STATS_DEFAULT_MAX_BAR_HEIGHT', 200);
define('ABNET_POST_STATS_DEFAULT_SHOW_TITLE', false);
define('ABNET_POST_STATS_DEFAULT_SHOW_SUMMARY', true);
define('ABNET_POST_STATS_DEFAULT_CHART_COLOR', '#005177');
