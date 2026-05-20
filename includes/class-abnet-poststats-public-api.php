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

	public function __construct(){
		$this->_plugin = _abnet_post_stats();
		$this->_statsDataSource = new ABNet_PostStats_DataSource();
		$this->_contentPillarDataSource = new ABNet_PostStats_ContentPillar_DataSource();
	}

	public function getPluginVersion(): string {
		// This will surface the plugin version as a stable capability check for consumers.
		return ABNET_POST_STATS_VERSION;
	}

	public function isPluginInitialized(): bool {
		return $this->_plugin->isInitialized();
	}

	public function getAvailableStyleMetricKeys(): array {
		// This should return the list of metric provider keys currently exposed by the plugin.
		throw new \LogicException('Not implemented yet.');
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
		return $this->_statsDataSource->getPostCountsPerMonth($limit);
	}

	public function getYearlyPostStats(int $limit): ABNet_PostStats_Result {
		return $this->_statsDataSource->getPostCountsPerYear($limit);
	}

	public function getStyleMetricsForPost(int $postId): array {
		// This should return the computed style metrics for a single post.
		throw new \LogicException('Not implemented yet.');
	}

	/**
	 * @return ABNet_PostStats_ContentPillar[]
	 */
	public function getContentPillars(): array {
		return $this->_contentPillarDataSource->getAllContentPillars();
	}

	public function getMonthlyContentPillarStats(int $pillarId, int $limit): ?ABNet_PostStats_Result {
		$contentPillar = $this->_contentPillarDataSource->getContentPillarById($pillarId);
		if ($contentPillar === null) {
			return null;
		}

		return $this->_statsDataSource->getContentPillarPostCountsPerMonth($contentPillar, $limit);
	}

	public function getYearlyContentPillarStats(int $pillarId, int $limit): ?ABNet_PostStats_Result {
		$contentPillar = $this->_contentPillarDataSource->getContentPillarById($pillarId);
		if ($contentPillar === null) {
			return null;
		}

		return $this->_statsDataSource->getContentPillarPostCountsPerMonth($contentPillar, $limit);
	}

	public function recomputeStyleMetricsForPost(int $postId): void {
		// This should force a recomputation of the stored style metrics for one post.
		throw new \LogicException('Not implemented yet.');
	}

	public function saveStyleMetricOptions(array $options): array {
		// This should persist a normalized style-metric option payload and return the saved state.
		throw new \LogicException('Not implemented yet.');
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
	
		$pillarId = $this->_contentPillarDataSource->createContentPillar($name, 
			$categoryIds, 
			$color, 
			$showByDefault);
		
		if ($pillarId === false || $pillarId <= 0) {
			return null;
		}

		return $this->_contentPillarDataSource->getContentPillarById($pillarId);
	}

	private function _validateContentPillarData(int $pillarId, string $name, array $categoryIds): WP_Error|null {
		if (empty($name)) {
			return new WP_Error('cp-name-empty', 'The content pillar name is empty.');
		}

		if (empty($categoryIds)) {
			return new WP_Error('cp-categories-empty', 'The content pillar category list is empty.');
		}

		if ($this->_contentPillarDataSource->contentPillarNameExists($name, $pillarId)) {
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

		$result = $this->_contentPillarDataSource->updateContentPillar($pillarId, 
			$name, 
			$categoryIds, 
			$color, 
			$showByDefault);

		if ($result !== false) {
			return $this->_contentPillarDataSource->getContentPillarById($pillarId);
		} else {
			return null;
		}
	}

	public function deleteContentPillar(int $pillarId): WP_Error|bool {
		if ($pillarId <= 0) {
			return new WP_Error('cp-id-empty', 'The content pillar ID is empty.');
		}

		$result = $this->_contentPillarDataSource->deleteContentPillar($pillarId);
		return $result !== false;
	}
	
}