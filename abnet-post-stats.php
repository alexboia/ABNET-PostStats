<?php
/**
 * Plugin Name: Simple Post Stats for WordPress
 * Plugin URI: https://github.com/alexboia/ABNET-PostStats
 * Description: A WordPress plugin for displaying simple content creation statistics.
 * Version: 1.1.0
 * Author: Alexandru Boia
 * Author URI: https://alexboia.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: abnet-post-stats
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

require_once 'abnet-post-stats-plugin-header.php';

require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-item.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-result.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-datasource.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-db.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-content-pillar.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-content-pillar-datasource.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-widget-manager.php';
require_once ABNET_POST_STATS_INC_DIR . 'style-metrics/feature.php';

require_once ABNET_POST_STATS_PLUGIN_DIR . 'abnet-post-stats-plugin-class.php';
require_once ABNET_POST_STATS_PLUGIN_DIR . 'abnet-post-stats-plugin-functions.php';

abnet_post_stats_run();