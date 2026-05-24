<?php
/**
 * Handles data retrieval operations for post statistics
 * 
 * @package ABNet_PostStats
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_PublicApi {
	private ?ABNet_PostStats $_plugin = null;

	private ?ABNet_PostStats_StyleMetricOptions $_options = null;

	private ?ABNet_PostStats_DataSource $_statsDataSource = null;

	private ?ABNet_PostStats_ContentPillar_DataSource $_contentPillarDataSource = null;

	private ?ABNet_PostStats_StyleInfoProvider $_styleInfoProvider = null;

	private ?ABNet_PostStats_StyleMetric_DataSource $_styleMetricsDataSource = null;

	public function __construct(){
		$this->_plugin = abnet_post_stats();
		$this->_statsDataSource = new ABNet_PostStats_DataSource();
		$this->_contentPillarDataSource = new ABNet_PostStats_ContentPillar_DataSource();
	}

	public function getPluginVersion(): string {
		return ABNET_POST_STATS_VERSION;
	}

	public function isPluginInitialized(): bool {
		return $this->_plugin->isInitialized();
	}

	public function getAvailableStyleMetricKeys(bool $onlyEnabled): array {
		return $this->_getStyleInfoProvider()->getAvailableStyleMetricKeys($onlyEnabled);
	}

	public function matchesEnabledProviders(ABNet_PostStats_StyleInfo $info): bool {
		return $this->_getStyleInfoProvider()->matchesEnabledProviders($info);
	}

	public function getStyleMetricOptions(): ABNet_PostStats_StyleMetricOptions {
		if ($this->_options === null) {
			$configured = ABNet_PostStats_StyleMetricOptions::configured();
			if (!$this->isPluginInitialized()) {
				return $configured;
			}

			$this->_options = $configured;
		}
		
		return $this->_options;
	}

	public function getMonthlyPostStats(int $limit): ABNet_PostStats_Result {
		return $this->_getStatsDataSource()->getPostCountsPerMonth($limit);
	}

	public function getYearlyPostStats(int $limit): ABNet_PostStats_Result {
		return $this->_getStatsDataSource()->getPostCountsPerYear($limit);
	}

	public function getStyleMetricMeta(string $key): ?ABNet_PostStats_StyleMetric_Meta {
		$provider = $this->_getStyleInfoProvider();
		$name = $provider->getName($key);
		if (empty($name)) {
			return null;
		}

		$description = $provider->getShortDescription($key) ?? "";
		return new ABNet_PostStats_StyleMetric_Meta($name, $description);
	}

	public function getStyleMetricsForPost(int $postId, bool $recomputeIfNotFound = false): ?ABNet_PostStats_StyleInfo {
		if ($postId <= 0) {
			return null;
		}

		$dataSource = $this->_getStyleMetricDataSource();
		$styleInfo = $dataSource->getStyleInfo($postId);
		if ($styleInfo === null && $recomputeIfNotFound === true) {
			$styleInfo = $this->recomputeStyleMetricsForPost($postId);
		}

		return $styleInfo;
	}

	/**
	 * @return ABNet_PostStats_ContentPillar[]
	 */
	public function getContentPillars(): array {
		return $this->_getContentPillarDataSource()->getAllContentPillars();
	}

	public function getMonthlyContentPillarStats(int $pillarId, int $limit): ?ABNet_PostStats_Result {
		$contentPillar = $this->_getContentPillarDataSource()
			->getContentPillarById($pillarId);

		if ($contentPillar === null) {
			return null;
		}

		return $this->_getStatsDataSource()
			->getContentPillarPostCountsPerMonth($contentPillar, 
				$limit);
	}

	public function getYearlyContentPillarStats(int $pillarId, int $limit): ?ABNet_PostStats_Result {
		$contentPillar = $this->_getContentPillarDataSource()
			->getContentPillarById($pillarId);

		if ($contentPillar === null) {
			return null;
		}

		return $this->_statsDataSource
			->getContentPillarPostCountsPerMonth($contentPillar, 
				$limit);
	}

	public function recomputeStyleMetricsForPost(int|\WP_Post $post): ?ABNet_PostStats_StyleInfo {
		if (is_numeric($post)) {
			if ($post <= 0) {
				return null;
			} else {
				$post = get_post($post);
			}
		}

		if ($post instanceof \WP_Post) {
			/**
			 * Filters the post content used as the source for style metric computation.
			 *
			 * @param string $postContent Raw post content that will be analyzed.
			 * @param WP_Post $post Current post instance.
			 * @param ABNet_PostStats_StyleMetricOptions $options Current style metric options.
			 */
			$postContent = apply_filters('abnet_poststats_style_metrics_source_content', 
				$post->post_content, 
				$post,
				$this->getStyleMetricOptions());

			if (empty($postContent)) {
				$postContent = $post->post_content;
			}
			
			$styleSource = new ABNet_PostStats_StyleSource($postContent);
			$styleInfoProvider = $this->_getStyleInfoProvider();
			
			$postId = intval($post->ID);
			$info = $styleInfoProvider->calculateStyleInfo($styleSource);

			if ($postId > 0) {
				$dataSource = $this->_getStyleMetricsDataSource();
				$result = $dataSource->saveStyleInfo($postId, $info);
				if (!$result) {
					abnet_write_log('[ERROR] Failed to save style metrics.');	
				} else {
					abnet_write_log('[INFO] Style metrics successfully saved.');
				}
			} else {
				abnet_write_log('[DEBUG] Post ID was empty. Style metrics info was not saved.');
			}

			return $info;
		} else {
			return null;
		}
	}

	public function saveStyleMetricOptions(array $options): ABNet_PostStats_StyleMetricOptions {
		return ABNet_PostStats_StyleMetricOptions::configure($options);
	}

	public function createContentPillar(string $name, 
		array $categoryIds, 
		string $color = ABNET_POST_STATS_DEFAULT_CHART_COLOR, 
		bool $showByDefault = true): ABNet_PostStats_ContentPillar|WP_Error|null {
		
		$validationError = $this->_validateContentPillarData(0, 
			$name, 
			$categoryIds);

		if ($validationError !== null) {
			return $validationError;
		}

		if (empty($color)) {
			$color = ABNET_POST_STATS_DEFAULT_CHART_COLOR;
		}
	
		$dataSource = $this->_getContentPillarDataSource();
		$pillarId = $dataSource->createContentPillar($name, 
			$categoryIds, 
			$color, 
			$showByDefault);
		
		if ($pillarId === false || $pillarId <= 0) {
			return null;
		}

		return $dataSource->getContentPillarById($pillarId);
	}

	private function _validateContentPillarData(int $pillarId, string $name, array $categoryIds): WP_Error|null {
		if (empty($name)) {
			return new WP_Error('cp-name-empty', 'The content pillar name is empty.');
		}

		if (empty($categoryIds)) {
			return new WP_Error('cp-categories-empty', 'The content pillar category list is empty.');
		}

		if ($this->_getContentPillarDataSource()->contentPillarNameExists($name, $pillarId)) {
			return new WP_Error('cp-name-exists', 
				'The given content pillar already exists', 
				compact( 
					'pillarId',
					'name', 
					'categoryIds'
				));
		}

		return null;
	}

	public function updateContentPillar(int $pillarId, 
		string $name, 
		array $categoryIds, 
		string $color = ABNET_POST_STATS_DEFAULT_CHART_COLOR, 
		bool $showByDefault = true): ABNet_PostStats_ContentPillar|WP_Error|null {
		
		if ($pillarId <= 0) {
			return new WP_Error('cp-id-empty', 'The content pillar ID is empty.');
		}

		$validationError = $this->_validateContentPillarData($pillarId, 
			$name, 
			$categoryIds);

		if ($validationError !== null) {
			return $validationError;
		}

		if (empty($color)) {
			$color = ABNET_POST_STATS_DEFAULT_CHART_COLOR;
		}

		$dataSource = $this->_getContentPillarDataSource();
		$result = $dataSource->updateContentPillar($pillarId, 
			$name, 
			$categoryIds, 
			$color, 
			$showByDefault);

		if ($result !== false) {
			return $dataSource->getContentPillarById($pillarId);
		} else {
			return null;
		}
	}

	public function deleteContentPillar(int $pillarId): WP_Error|bool {
		if ($pillarId <= 0) {
			return new WP_Error('cp-id-empty', 'The content pillar ID is empty.');
		}

		$result = $this->_getContentPillarDataSource()->deleteContentPillar($pillarId);
		return $result !== false;
	}

	private function _getStyleMetricDataSource(): ABNet_PostStats_StyleMetric_DataSource {
		if ($this->_styleMetricsDataSource === null) {
			$dataSource = new ABNet_PostStats_StyleMetric_DataSource($this->_getStyleInfoProvider());
			if (!$this->isPluginInitialized()) {
				return $dataSource;
			}

			$this->_styleMetricsDataSource = $dataSource;
		}

		return $this->_styleMetricsDataSource;
	}
	
	private function _getStyleInfoProvider(): ABNet_PostStats_StyleInfoProvider {
		if ($this->_styleInfoProvider === null) {
			$provider = new ABNet_PostStats_StyleInfoProvider($this->getStyleMetricOptions());
			if (!$this->isPluginInitialized()) {
				return $provider;
			}

			$this->_styleInfoProvider = $provider;
		}

		return $this->_styleInfoProvider;
	}

	private function _getStyleMetricsDataSource(): ABNet_PostStats_StyleMetric_DataSource {
		if ($this->_styleMetricsDataSource === null) {
			$dataSource = new ABNet_PostStats_StyleMetric_DataSource($this->_getStyleInfoProvider());
			if (!$this->isPluginInitialized()) {
				return $dataSource;
			}

			$this->_styleMetricsDataSource = $dataSource;
		}

		return $this->_styleMetricsDataSource;
	}

	private function _getStatsDataSource(): ABNet_PostStats_DataSource {
		return $this->_statsDataSource;
	}

	private function _getContentPillarDataSource(): ABNet_PostStats_ContentPillar_DataSource {
		return $this->_contentPillarDataSource;
	}
}