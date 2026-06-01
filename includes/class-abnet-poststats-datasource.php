<?php
/**
 * Handles data retrieval operations for post statistics
 * 
 * @package ABNet_PostStats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_DataSource {
	public function getPostCountsPerMonth(int $limit = 12): ABNet_PostStats_Result {
		if ($limit <= 0) {
			$limit = 12;
		}

		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;
		
		$sql = $wpdb->prepare(
			"SELECT 
				DATE_FORMAT(post_date, '%%Y-%%m') as month_key,
				COUNT(*) as post_count
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
				AND post_type = 'post'
				AND post_date_gmt >= DATE(DATE_SUB(NOW(), INTERVAL %d MONTH))
			GROUP BY DATE_FORMAT(post_date, '%%Y-%%m')
			ORDER BY month_key DESC
			LIMIT %d", 
			$limit, 
			$limit);

		/* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is prepared immediately above. */
		$rawResults = $wpdb->get_results($sql, ARRAY_A);

		$normalizedResults = array();
		for ($i = 0; $i < $limit; $i++) {
			$monthKey = gmdate('Y-m', strtotime("-$i month"));
			$normalizedResults[$monthKey] = 0;
		}

		foreach ($rawResults as $row) {
			$normalizedResults[$row['month_key']] = (int) $row['post_count'];
		}
		
		/**
		 * var ABNet_Post_Stats_Item[] $resultItems
		 * @see ABNet_PostStats_Item
		 */
		$resultItems = array();

		foreach ($normalizedResults as $month => $count) {
			if (count($resultItems) < $limit) {
				$resultItems[] = new ABNet_PostStats_Item(
					(int) $count, 
					$month, 
					ABNET_POST_STATS_DEFAULT_CHART_COLOR
				);
			}	
		}
		
		return new ABNet_PostStats_Result(
			__('Post Counts Per Month', 'abnet-post-stats'),
			$resultItems
		);
	}

	public function getPostCountsPerYear(int $limit = 5): ABNet_PostStats_Result {
		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;

		if ($limit <= 0) {
			$limit = 5;
		}
		
		$sql = $wpdb->prepare(
			"SELECT 
				YEAR(post_date) as year_key,
				COUNT(*) as post_count
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
				AND post_type = 'post'
				AND post_date_gmt >= DATE(DATE_SUB(NOW(), INTERVAL %d YEAR))
			GROUP BY YEAR(post_date)
			ORDER BY year_key DESC
			LIMIT %d", 
			$limit, 
			$limit
		);

		/* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is prepared immediately above. */
		$rawResults = $wpdb->get_results($sql, ARRAY_A);

		$normalizedResults = array();
		for ($i = 0; $i < $limit; $i++) {
			$yearKey = gmdate('Y', strtotime("-$i year"));
			$normalizedResults[$yearKey] = 0;
		}

		foreach ($rawResults as $row) {
			$normalizedResults[$row['year_key']] = (int) $row['post_count'];
		}

		$resultItems = array();
		foreach ($normalizedResults as $year => $count) {
			$resultItems[] = new ABNet_PostStats_Item(
				(int) $count, 
				$year, 
				ABNET_POST_STATS_DEFAULT_CHART_COLOR
			);
		}
		
		return new ABNet_PostStats_Result(
			__('Post Counts Per Year', 'abnet-post-stats'),
			$resultItems
		);
	}

	public function getContentPillarPostCountsPerMonth(ABNet_PostStats_ContentPillar $contentPillar, int $limit = 12): ABNet_PostStats_Result {
		if ($limit <= 0) {
			$limit = 12;
		}

		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;
		
		$categoryIds = $contentPillar->getCategoryIds();
		/* translators: Monthly posts stats subheading for a content pillar */
		$pillarTitle = sprintf(__('Posts in %s (Monthly)', 'abnet-post-stats'), 
			$contentPillar->getName());

		if (empty($categoryIds)) {
			return new ABNet_PostStats_Result(
				$pillarTitle,
				array()
			);
		}
		
		$categoryIdsPlaceholder = implode(',', 
			array_fill(0, 
				count($categoryIds), 
				'%d')
		);
		
		/* phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $categoryIdsPlaceholder is a dynamically built list of placeholders. */
		/* phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Placeholders and values are dynamically built. */
		
		$sql = $wpdb->prepare(
			"SELECT 
				DATE_FORMAT(p.post_date, '%%Y-%%m') as month_key,
				COUNT(DISTINCT p.ID) as post_count
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.post_status = 'publish' 
				AND p.post_type = 'post'
				AND p.post_date_gmt >= DATE(DATE_SUB(NOW(), INTERVAL %d MONTH))
				AND tt.taxonomy = 'category'
				AND tt.term_id IN ($categoryIdsPlaceholder)
			GROUP BY DATE_FORMAT(p.post_date, '%%Y-%%m')
			ORDER BY month_key DESC
			LIMIT %d", 
			array_merge(array($limit), $categoryIds, array($limit))
		);

		/* phpcs:enable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber */
		/* phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared */

		/* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is prepared immediately above. */
		$rawResults = $wpdb->get_results($sql, ARRAY_A);

		$normalizedResults = array();
		for ($i = 0; $i < $limit; $i++) {
			$monthKey = gmdate('Y-m', strtotime("-$i month"));
			$normalizedResults[$monthKey] = 0;
		}

		foreach ($rawResults as $row) {
			$normalizedResults[$row['month_key']] = (int) $row['post_count'];
		}
		
		$resultItems = array();

		foreach ($normalizedResults as $month => $count) {
			if (count($resultItems) < $limit) {
				$resultItems[] = new ABNet_PostStats_Item(
					(int) $count, 
					$month, 
					$contentPillar->getColor()
				);
			}
		}
		
		return new ABNet_PostStats_Result(
			$pillarTitle,
			$resultItems
		);
	}

	public function getContentPillarPostCountsPerYear(ABNet_PostStats_ContentPillar $contentPillar, int $limit = 5): ABNet_PostStats_Result {
		if ($limit <= 0) {
			$limit = 5;
		}

		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;
		
		$categoryIds = $contentPillar->getCategoryIds();
		/* translators: Yearly posts stats subheading for a content pillar */
		$pillarTitle = sprintf(__('Posts in %s (Yearly)', 'abnet-post-stats'), 
			$contentPillar->getName());

		if (empty($categoryIds)) {
			return new ABNet_PostStats_Result(
				$pillarTitle,
				array()
			);
		}
		
		$categoryIdsPlaceholder = implode(',', 
			array_fill(0, 
			count($categoryIds), 
			'%d'));
		
		/* phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $categoryIdsPlaceholder is a dynamically built list of placeholders. */
		/* phpcs:disable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Placeholders and values are dynamically built. */

		$sql = $wpdb->prepare(
			"SELECT 
				YEAR(p.post_date) as year_key,
				COUNT(DISTINCT p.ID) as post_count
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			WHERE p.post_status = 'publish' 
				AND p.post_type = 'post'
				AND p.post_date_gmt >= DATE(DATE_SUB(NOW(), INTERVAL %d YEAR))
				AND tt.taxonomy = 'category'
				AND tt.term_id IN ($categoryIdsPlaceholder)
			GROUP BY YEAR(p.post_date)
			ORDER BY year_key DESC
			LIMIT %d", 
			array_merge(array($limit), $categoryIds, array($limit))
		);

		/* phpcs:enable WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber */
		/* phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared */
		
		/* phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is prepared immediately above. */
		$rawResults = $wpdb->get_results($sql, ARRAY_A);

		$normalizedResults = array();
		for ($i = 0; $i < $limit; $i++) {
			$yearKey = gmdate('Y', strtotime("-$i year"));
			$normalizedResults[$yearKey] = 0;
		}

		foreach ($rawResults as $row) {
			$normalizedResults[$row['year_key']] = (int) $row['post_count'];
		}

		$resultItems = array();
		foreach ($normalizedResults as $year => $count) {
			$resultItems[] = new ABNet_PostStats_Item(
				(int) $count, 
				$year, 
				$contentPillar->getColor()
			);
		}
		
		return new ABNet_PostStats_Result(
			$pillarTitle,
			$resultItems
		);
	}
}