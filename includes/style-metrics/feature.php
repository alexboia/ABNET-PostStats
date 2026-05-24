<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Get the current directory
$abnetStyleMetricsCurrentDir = dirname(__FILE__);
$abnetstyleMetrics = new ABNet_PostStats_Feature($abnetStyleMetricsCurrentDir);
$abnetstyleMetrics->setup();