<?php
/**
 * Database operations for ABNet Post Stats
 * 
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_Db {
	public function migrateContentPillarsTable(): void {
		global $wpdb;
		$this->_ensureColumn(
			'content_pillar_color', 
			sprintf("VARCHAR(7) DEFAULT '%s'", ABNET_POST_STATS_DEFAULT_CHART_COLOR)
		);

		$this->_ensureColumn(
			'show_by_default', 
			"TINYINT(4) NOT NULL DEFAULT '1'"
		);
	}

	private function _ensureColumn($columnName, $defIfNotExists) {
		global $wpdb;
		$tableName = $wpdb->prefix . 'abnet_post_stats_content_pillars';
		
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
		global $wpdb;
		
		$tableName = $wpdb->prefix . 'abnet_post_stats_content_pillars';
		$charsetCollate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS $tableName (
			content_pillar_id int(11) NOT NULL AUTO_INCREMENT,
			content_pillar_name varchar(250) NOT NULL,
			content_pillar_definition text NOT NULL,
			content_pillar_color varchar(7) DEFAULT '#3498db',
			show_by_default TINYINT(4) NOT NULL DEFAULT '1',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (content_pillar_id),
			UNIQUE KEY content_pillar_name (content_pillar_name)
		) $charsetCollate;";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
