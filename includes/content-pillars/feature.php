<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

// Get the current directory
$abnetContentPillarsCurrentDir = dirname(__FILE__);
$abnetContentPillars = new ABNet_PostStats_Feature($abnetContentPillarsCurrentDir);
$abnetContentPillars->setup();