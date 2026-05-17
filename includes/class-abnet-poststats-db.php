<?php
/**
 * Database operations for ABNet Post Stats
 * 
 * @package ABNet_PostStats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_Db {
	public static function getStyleMetricsTableName(): string {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		return $wpdb->prefix . 'abnet_post_stats_style_metrics';
	}

	public static function getContentPillarsTableName(): string {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;	
		$tableName = $wpdb->prefix . 'abnet_post_stats_content_pillars';
		return $tableName;
	}

	public function migrateContentPillarsTable(): void {
		$tableName = self::getContentPillarsTableName();

		$this->_ensureColumn(
			$tableName,
			'content_pillar_color', 
			sprintf("VARCHAR(7) DEFAULT '%s'", ABNET_POST_STATS_DEFAULT_CHART_COLOR)
		);

		$this->_ensureColumn(
			$tableName,
			'show_by_default', 
			"TINYINT(4) NOT NULL DEFAULT '1'"
		);
	}

	private function _ensureColumn(string $tableName, string $columnName, bool $defIfNotExists): void {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		
		// Check if color column exists
		$columnExists = $wpdb->get_var($wpdb->prepare("
			SELECT COLUMN_NAME 
			FROM INFORMATION_SCHEMA.COLUMNS 
			WHERE TABLE_SCHEMA = %s 
			AND TABLE_NAME = %s 
			AND COLUMN_NAME = '$columnName'
		", DB_NAME, $tableName));
		
		// Add color column if it doesn't exist
		if (!$columnExists) {
			$wpdb->query("
				ALTER TABLE $tableName 
				ADD COLUMN $defIfNotExists
				AFTER content_pillar_definition
			");
		}
	}

	public function createContentPillarsTable(): void {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		
		$tableName = self::getContentPillarsTableName();
		$charsetCollate = $wpdb->get_charset_collate();
		$defaultColor = ABNET_POST_STATS_DEFAULT_CHART_COLOR;
		
		$sql = "CREATE TABLE IF NOT EXISTS $tableName (
			content_pillar_id int(11) NOT NULL AUTO_INCREMENT,
			content_pillar_name varchar(250) NOT NULL,
			content_pillar_definition text NOT NULL,
			content_pillar_color varchar(7) DEFAULT '$defaultColor',
			show_by_default TINYINT(4) NOT NULL DEFAULT '1',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (content_pillar_id),
			UNIQUE KEY content_pillar_name (content_pillar_name)
		) $charsetCollate;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	public function migrateStyleMetricsTable(): void {
		return;
	}

	public function createStyleMetricsTable(): void {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		
		$tableName = self::getStyleMetricsTableName();
		$charsetCollate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS  $tableName (
			metric_id int(11) NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			metric_key varchar(100) NOT NULL,
			metric_value FLOAT NOT NULL DEFAULT 0,
			metric_unit varchar(250) NOT NULL,
			metric_friendly_representation text NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (metric_id),
			UNIQUE KEY stat_style_metric_post_key (post_id, metric_key)
		) $charsetCollate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
