<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Get the current directory
$contentPillarsCurrentDir = dirname(__FILE__);
$contentPillars = new ABNet_PostStats_Feature($contentPillarsCurrentDir);
$contentPillars->setup();