<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Get the current directory
$styleMetricsCurrentDir = dirname(__FILE__);
$styleMetrics = new ABNet_PostStats_Feature($styleMetricsCurrentDir);
$styleMetrics->setup();