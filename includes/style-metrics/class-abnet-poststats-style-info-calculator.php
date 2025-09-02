<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_StyleInfoCalculator {
	private ABNet_PostStats_StyleMetricOptions $_options;

	public function __construct(ABNet_PostStats_StyleMetricOptions $options) {
		$this->_options = $options;
	}

	public function calculateStyleInfo(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleInfo {
		/**
		 * @var ABNet_PostStats_StyleMetric[] $metrics
		 */
		$styleMetrics = array();
		$providers = $this->_getProviders();

		foreach ($providers as $p) {
			$styleMetrics[] = $p->compute($source);
		}

		return new ABNet_PostStats_StyleInfo($styleMetrics);
	}

	/**
	 * @return ABNet_PostStats_StyleMetricProvider[]
	 */
	private function _getProviders(): array {
		$providers = [];
		if ($this->_options->getUseAverageSentenceLength()) {
			$providers[] = new ABNet_PostStats_StyleMetricAverageSentenceLengthProvider();
		}
		
		if ($this->_options->getUseEntropy()) {
			$providers[] = new ABNet_PostStats_StyleMetricEntropyProvider();
		}
		
		if ($this->_options->getUseNegativity()) {
			$negativeWordList = $this->_options->getNegativeWordList();
			$providers[] = new ABNet_PostStats_StyleMetricNegativityProvider($negativeWordList);
		}
		
		if ($this->_options->getUsePunctuation()) {
			$providers[] = new ABNet_PostStats_StyleMetricPunctuationProvider();
		}		
		
		$providers = apply_filters('abnet_posts_stats_style_metric_providers', 
			$providers, 
			$this->_options);

		$providers = array_filter($providers, function($provider) {
			return $provider instanceof ABNet_PostStats_StyleMetricProvider;
		});
		
		return $providers;
	}
}