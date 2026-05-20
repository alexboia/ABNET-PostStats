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

class ABNet_PostStats_StyleMetric_DataSource {
	
	private ABNet_PostStats_StyleInfoProvider $_styleInfoProvider;
	
	public function __construct(ABNet_PostStats_StyleInfoProvider $styleInfoProvider) {
		$this->_styleInfoProvider = $styleInfoProvider;
	}

	private function _getTableName(): string {
		return ABNet_PostStats_Db::getStyleMetricsTableName();
	}

	public function getStyleInfo(int $postId): ABNet_PostStats_StyleInfo|null {
		if (empty($postId) || $postId <= 0) {
			return null;
		}

		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;	
		$tableName = $this->_getTableName();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT metric_key, metric_value, metric_unit, metric_friendly_representation 
				 FROM {$tableName} 
				 WHERE post_id = %d",
				$postId
			),
			ARRAY_A
		);
		
		$metrics = array();
		
		if (!empty($results) && is_array($results)) {
			foreach ($results as $row) {
				$metricKey = $row['metric_key'];
				$metricName = $this->_styleInfoProvider->getName($metricKey);
				$metricDescription = $this->_styleInfoProvider->getShortDescription($metricKey);
				$bracket = $this->_styleInfoProvider->getBracket($metricKey);
				
				// Skip metrics where name/description lookup fails
				if ($metricName === null || $metricDescription === null) {
					continue;
				}

				// Skip disabled metrics
				if (!$this->_styleInfoProvider->isProviderEnabled($metricKey)) {
					continue;
				}
				
				$metrics[] = new ABNet_PostStats_StyleMetric(
					$metricKey,
					$metricName,
					$metricDescription,
					(float) $row['metric_value'],
					$row['metric_unit'],
					$row['metric_friendly_representation'],
					$bracket
				);
			}
		} else {
			return null;
		}
		
		return new ABNet_PostStats_StyleInfo($metrics);
	}
	
	/**
	 * @return int|false Number of affected rows on success, false on failure
	 */
	public function saveStyleInfo(int $postId, ABNet_PostStats_StyleInfo $styleInfo): int|false {
		if (empty($postId) || $postId < 0) {
			return false;
		}
		
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$tableName = $this->_getTableName();

		$wpdb->query('START TRANSACTION');
		
		try {
			// Delete existing metrics for this post
			$deleteResult = $wpdb->delete(
				$tableName,
				array('post_id' => $postId),
				array('%d')
			);
			
			if ($deleteResult === false) {
				$wpdb->query('ROLLBACK');
				return false;
			}
			
			$totalAffectedRows = $deleteResult;
			
			// Insert new metrics
			$metrics = $styleInfo->getMetrics();
			foreach ($metrics as $metric) {
				$insertResult = $wpdb->insert(
					$tableName,
					array(
						'post_id' => $postId,
						'metric_key' => $metric->getKey(),
						'metric_value' => $metric->getValue(),
						'metric_unit' => $metric->getUnit(),
						'metric_friendly_representation' => $metric->getFriendlyRepresentation()
					),
					array('%d', '%s', '%f', '%s', '%s')
				);
				
				if ($insertResult === false) {
					$wpdb->query('ROLLBACK');
					return false;
				}
				
				$totalAffectedRows += $insertResult;
			}
			
			$wpdb->query('COMMIT');
			
			return $totalAffectedRows;
			
		} catch (Exception $e) {
			$wpdb->query('ROLLBACK');
			error_log(sprintf('[ERROR] Error saving style metrics: %s [%s]. %s.', 
				$e->getMessage(), 
				$e->getCode(), 
				$e->getTraceAsString()));
			return false;
		}
	}
	
	/**
	 * @return int|false Number of affected rows on success, false on failure
	 */
	public function deleteStyleInfo(int $postId): int|false {
		if (empty($postId) || $postId < 0) {
			return false;
		}

		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;	
		$tableName = $this->_getTableName();

		return $wpdb->delete(
			$tableName,
			array('post_id' => $postId),
			array('%d')
		);
	}

	public function styleInfoExists(int $postId): bool {
		if (empty($postId) || $postId < 0) {
			return false;
		}

		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$tableName = $this->_getTableName();

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$tableName} WHERE post_id = %d",
				$postId
			)
		);
		
		return (int) $count > 0;
	}
	
	/**
	 * @return string[]
	 */
	public function getMetricKeys(int $postId): array {
		if (empty($postId) || $postId < 0) {
			return array();
		}

		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$tableName = $this->_getTableName();

		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT metric_key FROM {$tableName} WHERE post_id = %d ORDER BY metric_key",
				$postId
			)
		);
		
		return is_array($results) ? $results : array();
	}
	
	/**
	 * @param int[] $postIds
	 * @return int|false Number of affected rows on success, false on failure
	 */
	public function deleteStyleInfoForPosts(array $postIds): int|false {
		if (empty($postIds)) {
			return 0;
		}

		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$tableName = $this->_getTableName();
		$postIds = $this->_sanitizePostIds($postIds);
		
		if (empty($postIds)) {
			return 0;
		}
		
		$placeholders = implode(',', 
			array_fill(0, 
				count($postIds), 
				'%d'));

		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$tableName} WHERE post_id IN ({$placeholders})",
				...$postIds
			)
		);
	}
	
	/**
	 * @param int[] $postIds
	 * @return array<int, ABNet_PostStats_StyleInfo> Associative array with post_id as key and ABNet_PostStats_StyleInfo as value
	 */
	public function getStyleInfoForPosts(array $postIds): array {
		if (empty($postIds)) {
			return array();
		}

		global $wpdb;	
		$tableName = $this->_getTableName();		
		$postIds = $this->_sanitizePostIds($postIds);
		
		if (empty($postIds)) {
			return array();
		}
		
		$placeholders = implode(',', array_fill(0, count($postIds), '%d'));
		
		/**
		 * @var \wpdb $wpdb
		 */
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, metric_key, metric_value, metric_unit, metric_friendly_representation 
				 FROM {$tableName} 
				 WHERE post_id IN ({$placeholders})
				 ORDER BY post_id, metric_key",
				...$postIds
			),
			ARRAY_A
		);
		
		$styleInfoByPost = array();
		
		// Initialize empty arrays for all requested posts
		foreach ($postIds as $postId) {
			$styleInfoByPost[$postId] = array();
		}
		
		if (!empty($results) && is_array($results)) {
			// Group results by post_id
			foreach ($results as $row) {
				$postId = (int) $row['post_id'];
				$metricKey = $row['metric_key'];
				$metricName = $this->_styleInfoProvider->getName($metricKey);
				$metricDescription = $this->_styleInfoProvider->getShortDescription($metricKey);
				$bracket = $this->_styleInfoProvider->getBracket($metricKey);
				
				// Skip metrics where name/description lookup fails
				if ($metricName === null || $metricDescription === null) {
					continue;
				}
				
				$styleInfoByPost[$postId][] = new ABNet_PostStats_StyleMetric(
					$metricKey,
					$metricName,
					$metricDescription,
					(float) $row['metric_value'],
					$row['metric_unit'],
					$row['metric_friendly_representation'],
					$bracket
				);
			}
		}
		
		// Convert metric arrays to StyleInfo objects
		foreach ($styleInfoByPost as $postId => $metrics) {
			$styleInfoByPost[$postId] = new ABNet_PostStats_StyleInfo($metrics);
		}
		
		return $styleInfoByPost;
	}

	/**
	 * @param array $postIds 
	 * @return int[]
	 */
	private function _sanitizePostIds(array $postIds): array {
		$postIds = array_map('intval', $postIds);
		$postIds = array_filter($postIds, function($id) { 
			return $id > 0; 
		});
		return $postIds;
	}
}