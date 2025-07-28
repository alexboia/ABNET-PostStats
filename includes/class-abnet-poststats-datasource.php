<?php
/**
 * Handles data retrieval operations for post statistics
 * 
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Data source class for post statistics
 */
class ABNet_PostStats_DataSource {
	
	/**
	 * Get published post counts per month
	 * 
	 * @param int $limit Number of months to retrieve (default: 12)
	 * @return ABNet_Post_Stats_Result
	 */
	public function getPostCountsPerMonth(int $limit = 12): ABNet_Post_Stats_Result {
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
				AND post_date >= DATE_SUB(NOW(), INTERVAL %d MONTH)
			GROUP BY DATE_FORMAT(post_date, '%%Y-%%m')
			ORDER BY month_key DESC
			LIMIT %d", 
			$limit, 
			$limit);
		
		$rawResults = $wpdb->get_results($sql, ARRAY_A);

		$normalizedResults = array();
		for ($i = 0; $i < $limit; $i++) {
			$monthKey = date('Y-m', strtotime("-$i month"));
			$normalizedResults[$monthKey] = 0;
		}

		foreach ($rawResults as $row) {
			$normalizedResults[$row['month_key']] = (int) $row['post_count'];
		}
		
		/**
		 * var ABNet_Post_Stats_Item[] $resultItems
		 * @see ABNet_Post_Stats_Item
		 */
		$resultItems = array();
		foreach ($normalizedResults as $month => $count) {
			$resultItems[] = new ABNet_Post_Stats_Item((int) $count, $month);
		}
		
		return new ABNet_Post_Stats_Result(
			__('Post Counts Per Month', 'abnet-post-stats'),
			$resultItems
		);
	}
	
	/**
	 * Get published post counts per year
	 * 
	 * @param int $limit Number of years to retrieve (default: 5)
	 * @return ABNet_Post_Stats_Result
	 */
	public function getPostCountsPerYear(int $limit = 5): ABNet_Post_Stats_Result {
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
				AND post_date >= DATE_SUB(NOW(), INTERVAL %d YEAR)
			GROUP BY YEAR(post_date)
			ORDER BY year_key DESC
			LIMIT %d", 
			$limit, 
			$limit
		);
		
		$rawResults = $wpdb->get_results($sql, ARRAY_A);

		$normalizedResults = array();
		for ($i = 0; $i < $limit; $i++) {
			$yearKey = date('Y', strtotime("-$i year"));
			$normalizedResults[$yearKey] = 0;
		}

		foreach ($rawResults as $row) {
			$normalizedResults[$row['year_key']] = (int) $row['post_count'];
		}

		foreach ($normalizedResults as $year => $count) {
			$resultItems[] = new ABNet_Post_Stats_Item((int) $count, $year);
		}
		
		return new ABNet_Post_Stats_Result(
			__('Post Counts Per Year', 'abnet-post-stats'),
			$resultItems
		);
	}
}