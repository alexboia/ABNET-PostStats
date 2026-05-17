<?php
/**
 * @package ABNet_PostStats
 * @since 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_StyleInfoProvider {
	private ABNet_PostStats_StyleMetricOptions $_options;

	/**
	 * @var ABNet_PostStats_StyleMetricProvider[]
	 */
	private ?array $_providers = null;

	/**
	 * @var array<string, string>
	 */
	private ?array $_styleMetricDescriptionMap = null;

	/**
	 * @var array<string, string>
	 */
	private ?array $_styleMetricNameMap = null;

	public function __construct(ABNet_PostStats_StyleMetricOptions $options) {
		$this->_options = $options;
	}

	public function getShortDescription(string $key) {
		if (empty($key)) {
			return null;
		}

		if ($this->_styleMetricDescriptionMap === null) {
			$providers = $this->_getProviders();
			foreach ($providers as $p) {
				$this->_styleMetricDescriptionMap[$p->getKey()] = $p->getShortDescription();
			}
		}

		return $this->_styleMetricDescriptionMap[$key] ?? null;
	}

	public function getName(string $key) {
		if (empty($key)) {
			return null;
		}

		if ($this->_styleMetricNameMap === null) {
			$providers = $this->_getProviders();
			foreach ($providers as $p) {
				$this->_styleMetricNameMap[$p->getKey()] = $p->getName();
			}
		}

		return $this->_styleMetricNameMap[$key] ?? null;
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
		if ($this->_providers === null) {
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
			
			if ($this->_options->getUseLix()) {
				$providers[] = new ABNet_PostStats_StyleMetricLixProvider();
			}

			if ($this->_options->getUseYulesK()) {
				$yulsesKMultiplier = $this->_options->getYulesKMultiplier();
				$providers[] = new ABNet_PostStats_StyleMetricYulesKProvider($yulsesKMultiplier);
			}

			if ($this->_options->getUseHapaxToTypes()) {
				$providers[] = new ABNet_PostStats_StyleMetricHapaxToTypesProvider();
			}
			
			$providers = apply_filters('abnet_posts_stats_style_metric_providers', 
				$providers, 
				$this->_options);

			$providers = array_filter($providers, function($provider) {
				return $provider instanceof ABNet_PostStats_StyleMetricProvider;
			});

			$this->_providers = $providers;
		}		
		
		return $this->_providers;
	}
}