<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
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
				$aslBracket = $this->_options->getAverageSentenceLengthBracket();
				$providers[] = new ABNet_PostStats_StyleMetricAverageSentenceLengthProvider($aslBracket);
			}
			
			if ($this->_options->getUseEntropy()) {
				$entropyBracket = $this->_options->getEntropyBracket();
				$providers[] = new ABNet_PostStats_StyleMetricEntropyProvider($entropyBracket);
			}
			
			if ($this->_options->getUseNegativity()) {
				if ($this->_options->hasNegativeWordList()) {
					$negativeWordList = $this->_options->getNegativeWordList();
					$negativityBracket = $this->_options->getNegativityBracket();
					$providers[] = new ABNet_PostStats_StyleMetricNegativityProvider($negativeWordList, $negativityBracket);
				} else {
					write_log('[DEBUG] Negativity provider enabled, but no negative word list configured. Will not register.');
				}				
			}
			
			if ($this->_options->getUsePunctuation()) {
				$punctuationBracket = $this->_options->getPunctuationBracket();
				$providers[] = new ABNet_PostStats_StyleMetricPunctuationProvider($punctuationBracket);
			}
			
			if ($this->_options->getUseLix()) {
				$lixBracket = $this->_options->getLixBracket();
				$providers[] = new ABNet_PostStats_StyleMetricLixProvider($lixBracket);
			}

			if ($this->_options->getUseYulesK()) {
				$yulsesKMultiplier = $this->_options->getYulesKMultiplier();
				$yulesKBracket = $this->_options->getYulesKBracket();
				$providers[] = new ABNet_PostStats_StyleMetricYulesKProvider($yulsesKMultiplier, $yulesKBracket);
			}

			if ($this->_options->getUseHapaxToTypes()) {
				$hapaxBracket = $this->_options->getHapaxToTypesBracket();
				$providers[] = new ABNet_PostStats_StyleMetricHapaxToTypesProvider($hapaxBracket);
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