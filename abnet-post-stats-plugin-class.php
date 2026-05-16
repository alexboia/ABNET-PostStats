<?php
declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats {
	/**
	 * @var ABNet_PostStats
	 */
	private static $_instance = null;

	private ABNet_PostStats_DataSource $_dataSource;

	private ABNet_PostStats_ContentPillar_DataSource $_contentPillarDataSource;

	private ABNet_PostStats_WidgetManager $_widgetManger;

	private ABNet_PostStats_ContentPillar_Manager $_contentPillarManager;

	public static function getInstance(): ABNet_PostStats {
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		$this->_dataSource = new ABNet_PostStats_DataSource();
		$this->_contentPillarDataSource = new ABNet_PostStats_ContentPillar_DataSource();
		$this->_contentPillarManager = new ABNet_PostStats_ContentPillar_Manager();
		$this->_widgetManger = new ABNet_PostStats_WidgetManager($this->_dataSource, $this->_contentPillarDataSource);
	}

	public function run(): void {
		register_activation_hook(ABNET_POST_STATS_PLUGIN_FILE, array($this, 'activate'));
		add_action('plugins_loaded', array($this, 'init'));
	}

	public function activate(): void {
		$this->_createContentPillarsTable();
		$this->_migrateContentPillarsTable();
		do_action('abnet_post_stats_activated');
	}

	private function _createContentPillarsTable(): void {
		$db = new ABNet_PostStats_Db();
		$db->createContentPillarsTable();
	}

	private function _migrateContentPillarsTable(): void {
		$db = new ABNet_PostStats_Db();
		$db->migrateContentPillarsTable();
	}

	public function init(): void {
		load_plugin_textdomain('abnet-post-stats', false, dirname(plugin_basename(__FILE__)) . '/languages');
		$this->_initHooks();
	}

	private function _initHooks(): void {
		add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
		add_action('admin_menu', array($this, 'addAdminMenu'));

		$this->_widgetManger->init();
	}

	private function _shouldIncludeDashboardWidgets(): bool {
		return $this->_widgetManger->shouldIncludeDashboardWidgets();
	}

	public function addAdminMenu(): void {
		$this->_contentPillarManager->setupMenu();
	}

	public function enqueueAdminScripts(): void {
		$isOnContentPillarsPage = $this->_contentPillarManager
			->setupScriptsAndStyles();
		
		$includeAdminCss = $this->_shouldIncludeDashboardWidgets() 
			|| $isOnContentPillarsPage;
		
		if ($includeAdminCss) {
			wp_enqueue_style(
				'abnet-post-stats-admin',
				ABNET_POST_STATS_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				ABNET_POST_STATS_VERSION
			);
		}
	}
}