<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

interface ABNet_PostStats_StyleMetricProvider {
	function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric;

	function getKey(): string;

	function getName(): string;

	function getShortDescription(): string;
}