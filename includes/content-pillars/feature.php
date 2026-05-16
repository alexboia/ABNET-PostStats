<?php
if (!defined('ABSPATH')) {
	exit;
}

// Get the current directory
$currentDir = dirname(__FILE__);
$contentPillars = new ABNet_PostStats_Feature($currentDir);
$contentPillars->setup();