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

	/**
	 * @var array<string, ABNet_PostStats_StyleMetricBracket>
	 */
	private ?array $_styleMetricBracketMap = null;

	/**
	 * @var array<string, bool>
	 */
	private array $_enabledProviders = array();

	public function __construct(ABNet_PostStats_StyleMetricOptions $options) {
		$this->_options = $options;
	}

	public function getShortDescription(string $key): ?string {
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

	public function getBracket(string $key): ABNet_PostStats_StyleMetricBracket {
		if (empty($key)) {
			return ABNet_PostStats_StyleMetricBracket::unbounded();
		}

		if ($this->_styleMetricBracketMap === null) {
			$providers = $this->_getProviders();
			foreach ($providers as $p) {
				$this->_styleMetricBracketMap[$p->getKey()] = $p->getBracket();
			}
		}

		return $this->_styleMetricBracketMap[$key]
			?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}

	public function getName(string $key): ?string {
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
			if ($this->isProviderEnabled($p->getKey())) {
				$styleMetrics[] = $p->compute($source);
			}
		}

		return new ABNet_PostStats_StyleInfo($styleMetrics);
	}

	public function matchesEnabledProviders(ABNet_PostStats_StyleInfo $info): bool {
		$enabledProviderCount = 0;
		$enabledProviders = $this->_getEnabledProviders();

		foreach ($enabledProviders as $key => $enabled) {
			if ($enabled) {
				$enabledProviderCount ++;
			}
		}

		$givenMetrics = $info->getMetrics();
		if ($enabledProviderCount !== count($givenMetrics)) {
			return false;
		}

		foreach ($givenMetrics as $m) {
			$key = $m->getKey();
			if (!isset($enabledProviders[$key]) || $enabledProviders[$key] !== true) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return ABNet_PostStats_StyleMetricProvider[]
	 */
	private function _getProviders(): array {
		$this->_initProvidersIfNeeded();
		return $this->_providers;
	}

	/**
	 * @return array<string, bool>
	 */
	private function _getEnabledProviders(): array {
		$this->_initProvidersIfNeeded();
		return $this->_enabledProviders;
	}

	private function _initProvidersIfNeeded(): void {
		if ($this->_providers === null) {
			$providers = [];
			$enabledProviders = [];

			$aslBracket = $this->_options->getAverageSentenceLengthBracket();
			$aslProvider = new ABNet_PostStats_StyleMetricAverageSentenceLengthProvider($aslBracket);
			$providers[] = $aslProvider;
			$enabledProviders[$aslProvider->getKey()] = $this->_options->getUseAverageSentenceLength();

			$entropyBracket = $this->_options->getEntropyBracket();
			$entropyProvider = new ABNet_PostStats_StyleMetricEntropyProvider($entropyBracket);
			$providers[] = $entropyProvider;
			$enabledProviders[$entropyProvider->getKey()] = $this->_options->getUseEntropy();

			$negativeWordList = $this->_options->getNegativeWordList();
			$negativityBracket = $this->_options->getNegativityBracket();
			$negativityProvider = new ABNet_PostStats_StyleMetricNegativityProvider($negativeWordList, $negativityBracket);
			$providers[] = $negativityProvider;
			$enabledProviders[$negativityProvider->getKey()] = $this->_options->getUseNegativity();

			$punctuationBracket = $this->_options->getPunctuationBracket();
			$punctuationProvider = new ABNet_PostStats_StyleMetricPunctuationProvider($punctuationBracket);
			$providers[] = $punctuationProvider;
			$enabledProviders[$punctuationProvider->getKey()] = $this->_options->getUsePunctuation();
			
			$lixBracket = $this->_options->getLixBracket();
			$lixProvider = new ABNet_PostStats_StyleMetricLixProvider($lixBracket);
			$providers[] = $lixProvider;
			$enabledProviders[$lixProvider->getKey()] = $this->_options->getUseLix();

			$yulsesKMultiplier = $this->_options->getYulesKMultiplier();
			$yulesKBracket = $this->_options->getYulesKBracket();
			$yulesKProvider = new ABNet_PostStats_StyleMetricYulesKProvider($yulsesKMultiplier, $yulesKBracket);
			$providers[] = $yulesKProvider;
			$enabledProviders[$yulesKProvider->getKey()] = $this->_options->getUseYulesK();

			$hapaxBracket = $this->_options->getHapaxToTypesBracket();
			$hapaxProvider = new ABNet_PostStats_StyleMetricHapaxToTypesProvider($hapaxBracket);
			$providers[] = $hapaxProvider;
			$enabledProviders[$hapaxProvider->getKey()] = $this->_options->getUseHapaxToTypes();
			
			/**
			 * Filters the list of registered style metric providers.
			 *
			 * @param ABNet_PostStats_StyleMetricProvider[] $providers Registered provider instances.
			 * @param ABNet_PostStats_StyleMetricOptions $options Current style metric options.
			 */
			$providers = apply_filters('abnet_posts_stats_style_metric_providers', 
				$providers, 
				$this->_options);

			$providers = array_filter($providers, function($provider) {
				return $provider instanceof ABNet_PostStats_StyleMetricProvider;
			});

			/**
			 * Filters the list of enabled style metric providers. 
			 * 	Keys are provider keys (getKey()), values are boolean true or false.
			 *	This will also affect what's displayed, as well as what's computed.
			 *
			 * @param array<string, bool> $enabledProviders Active provider status map.
			 * @param ABNet_PostStats_StyleMetricProvider[] $providers Registered provider instances.
			 * @param ABNet_PostStats_StyleMetricOptions $options Current style metric options.
			 */
			$enabledProviders = apply_filters('abnet_posts_stats_style_metric_enabled_providers', 
				$enabledProviders, 
				$providers,
				$this->_options);

			foreach ($providers as $p) {
				$key = $p->getKey();
				if (!isset($enabledProviders[$key])) {
					$enabledProviders[$key] = false;
				} else {
					$enabledProviders[$key] = ($enabledProviders[$key] === true);
				}
			}

			$this->_providers = $providers;
			$this->_enabledProviders = $enabledProviders;
		}	
	}

	public function isProviderEnabled(string $key): bool {
		return isset($this->_enabledProviders[$key]) 
			&& $this->_enabledProviders[$key] === true;
	}

	public function getAvailableStyleMetricKeys(bool $onlyEnabled = false): array {
		$keys = array();
		$providers = $this->_getProviders();
		$enabledProviders = $this->_getEnabledProviders();

		if ($onlyEnabled) {
			return array_keys($enabledProviders);
		}

		foreach ($providers as $p) {
			$keys[] = $p->getKey();
		}

		return $keys;
	}
}