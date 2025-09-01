<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_StyleInfo {
    /**
     * @var ABNet_PostStats_StyleMetric[]
     */
    private array $_metrics;

    /**
     * @param ABNet_PostStats_StyleMetric[] $metrics 
     */
    public function __construct(array $metrics) {
        $this->_metrics = $metrics;
    }

    /**
     * @return ABNet_PostStats_StyleMetric[]
     */
    public function getMetrics(): array {
        return $this->_metrics;
    }
}