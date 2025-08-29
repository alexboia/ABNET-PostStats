<?php
/**
 * Handles data operations for content pillars
 * 
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_ContentPillar_DataSource {
	
	private function _getTableName(): string {
		global $wpdb;
		return $wpdb->prefix . 'abnet_post_stats_content_pillars';
	}
	
	/**
	 * @return ABNet_Post_Stats_Content_Pillar[]
	 */
	public function getAllContentPillars(): array {
		global $wpdb;
		$tableName = $this->_getTableName();
		
		$dbRows = $wpdb->get_results(
			"SELECT * FROM $tableName ORDER BY content_pillar_name ASC",
			ARRAY_A
		);
		
		return !empty($dbRows) && is_array($dbRows) 
			? ABNet_Post_Stats_Content_Pillar::fromResultList($dbRows)
			: array();
	}
	
	/**
	 * @return ABNet_Post_Stats_Content_Pillar|null
	 */
	public function getContentPillarById(int $id): ?ABNet_Post_Stats_Content_Pillar {
		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;

		$tableName = $this->_getTableName();
		
		$dbRow = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM $tableName WHERE content_pillar_id = %d", $id),
			ARRAY_A
		);
		
		return !empty($dbRow) 
			? new ABNet_Post_Stats_Content_Pillar($dbRow) 
			: null;
	}

	public function createContentPillar(string $name, array $categoryIds, string $color, bool $showByDefault = true): int|false {
		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;

		$tableName = $this->_getTableName();
		$definition = $this->_encodeCategoryIds($categoryIds);
		
		$result = $wpdb->insert(
			$tableName,
			array(
				'content_pillar_name' => sanitize_text_field($name),
				'content_pillar_definition' => $definition,
				'content_pillar_color' => sanitize_hex_color($color) ?: '#3498db',
				'show_by_default' => $showByDefault ? 1 : 0
			),
			array('%s', '%s', '%s')
		);
		
		return $result !== false ? $wpdb->insert_id : false;
	}

	private function _encodeCategoryIds(array $categoryIds): string {
		return json_encode(array(
			'categories' => array_unique(
				array_map('intval', $categoryIds)
			)
		));
	}

	public function updateContentPillar(int $id, string $name, array $categoryIds, string $color, bool $showByDefault): int|false {
		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;

		$tableName = $this->_getTableName();
		$definition = $this->_encodeCategoryIds($categoryIds);
		
		return $wpdb->update(
			$tableName,
			array(
				'content_pillar_name' => sanitize_text_field($name),
				'content_pillar_definition' => $definition,
				'content_pillar_color' => sanitize_hex_color($color) ?: '#3498db',
				'show_by_default' => $showByDefault ? 1 : 0
			),
			array('content_pillar_id' => $id),
			array('%s', '%s', '%s'),
			array('%d')
		);
	}

	public function deleteContentPillar(int $id): int|false {
		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;
		$tableName = $this->_getTableName();
		
		return $wpdb->delete(
			$tableName,
			array('content_pillar_id' => $id),
			array('%d')
		);
	}

	public function contentPillarNameExists(string $name, int $excludeId = 0): bool {
		/**
		 * @var \wpdb $wpdb
		 * @see https://developer.wordpress.org/reference/classes/wpdb/
		 */
		global $wpdb;
		$tableName = $this->_getTableName();
		
		$sql = "SELECT COUNT(*) FROM $tableName WHERE content_pillar_name = %s";
		$params = array($name);
		
		if ($excludeId > 0) {
			$sql .= " AND content_pillar_id != %d";
			$params[] = $excludeId;
		}
		
		$count = $wpdb->get_var($wpdb->prepare($sql, $params));
		return intval($count) > 0;
	}
}
