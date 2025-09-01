<?php
declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_Post_Stats {
	private const DEFAULT_MONTLY_UPPER_LIMIT = 6;

	private const DEFAULT_YEARLY_UPPER_LIMIT = 5;
	
	/**
	 * @var ABNet_Post_Stats
	 */
	private static $_instance = null;

	private ABNet_PostStats_DataSource $_dataSource;

	private ABNet_PostStats_ContentPillar_DataSource $_contentPillarDataSource;

	private ABNet_PostStats_WidgetManager $_widgetManger;

	public static function getInstance(): ABNet_Post_Stats {
		if (null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		$this->_dataSource = new ABNet_PostStats_DataSource();
		$this->_contentPillarDataSource = new ABNet_PostStats_ContentPillar_DataSource();
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
		add_options_page(
			__('Simple Post Stats - Content Pillars Definitions', 'abnet-post-stats'),
			__('Simple Post Stats - Content Pillars Definitions', 'abnet-post-stats'),
			'manage_options',
			'abnet-post-stats-content-pillars',
			array($this, 'renderContentPillarsPage')
		);
	}

	public function renderContentPillarsPage(): void {
		$message = '';
		$messageType = '';
		
		// Process form submissions
		if (!empty($_SERVER['REQUEST_METHOD']) &&
			$_SERVER['REQUEST_METHOD'] === 'POST' && 
			isset($_POST['abnet_content_pillar_nonce'])) {
			$result = $this->_processContentPillarForm();
			$message = $result['message'];
			$messageType = $result['type'];
		}
		
		// Get data for the view
		$contentPillars = $this->_contentPillarDataSource->getAllContentPillars();
		$categories = get_categories(array(
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC'
		));
		$mostUsedCategories = get_categories(array(
			'hide_empty' => false,
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 10
		));
		
		// Get editing pillar if edit mode
		$editingPillar = null;
		if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
			$editingPillar = $this->_contentPillarDataSource->getContentPillarById(intval($_GET['edit']));
		}
		
		// Include the view
		require ABNET_POST_STATS_VIEWS_DIR . '/admin-content-pillars.php';
	}

	private function _processContentPillarForm(): array {
		$nonce = $_POST['abnet_content_pillar_nonce'] ?? '';
		if (!wp_verify_nonce($nonce, 'abnet_content_pillar_action')) {
			return array(
				'message' => __('Security check failed.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		$action = sanitize_text_field($_POST['action'] ?? '');
		switch ($action) {
			case 'create':
				return $this->_processCreateContentPillar();
			case 'update':
				return $this->_processUpdateContentPillar();
			case 'delete':
				return $this->_processDeleteContentPillar();
			default:
				return array(
					'message' => __('Invalid action.', 'abnet-post-stats'),
					'type' => 'error'
				);
		}
	}
	
	/**
	 * @return array Array with 'message' and 'type' keys
	 */
	private function _processCreateContentPillar(): array {
		$name = sanitize_text_field($_POST['pillar_name'] ?? '');
		$categoryIds = array_map('intval', $_POST['category_ids'] ?? array());
		$color = sanitize_text_field($_POST['pillar_color'] ?? ABNET_POST_STATS_DEFAULT_CHART_COLOR);
		$showByDefault = !empty($_POST['show_by_default']) && $_POST['show_by_default'] === 'yes';
		
		if (empty($name)) {
			return array(
				'message' => __('Content pillar name is required.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if (empty($categoryIds)) {
			return array(
				'message' => __('At least one category must be selected.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if ($this->_contentPillarDataSource->contentPillarNameExists($name)) {
			return array(
				'message' => __('A content pillar with this name already exists.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		$result = $this->_contentPillarDataSource->createContentPillar($name, $categoryIds, $color, $showByDefault);
		if ($result) {
			return array(
				'message' => __('Content pillar created successfully.', 'abnet-post-stats'),
				'type' => 'success'
			);
		} else {
			return array(
				'message' => __('Failed to create content pillar.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
	}
	
	private function _processUpdateContentPillar(): array {
		$id = intval($_POST['pillar_id'] ?? 0);
		$name = sanitize_text_field($_POST['pillar_name'] ?? '');
		$categoryIds = array_map('intval', $_POST['category_ids'] ?? array());
		$color = sanitize_text_field($_POST['pillar_color'] ?? ABNET_POST_STATS_DEFAULT_CHART_COLOR);
		$showByDefault = !empty($_POST['show_by_default']) && $_POST['show_by_default'] === 'yes';
		
		if (empty($name)) {
			return array(
				'message' => __('Content pillar name is required.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if (empty($categoryIds)) {
			return array(
				'message' => __('At least one category must be selected.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if ($this->_contentPillarDataSource->contentPillarNameExists($name, $id)) {
			return array(
				'message' => __('A content pillar with this name already exists.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		$result = $this->_contentPillarDataSource->updateContentPillar($id, $name, $categoryIds, $color, $showByDefault);
		if ($result !== false) {
			return array(
				'message' => __('Content pillar updated successfully.', 'abnet-post-stats'),
				'type' => 'success'
			);
		} else {
			return array(
				'message' => __('Failed to update content pillar.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
	}

	private function _processDeleteContentPillar(): array {
		$id = intval($_POST['pillar_id'] ?? 0);
		$result = $this->_contentPillarDataSource->deleteContentPillar($id);
		
		if ($result) {
			return array(
				'message' => __('Content pillar deleted successfully.', 'abnet-post-stats'),
				'type' => 'success'
			);
		} else {
			return array(
				'message' => __('Failed to delete content pillar.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
	}

	public function enqueueAdminScripts(): void {
		/**
		 * @var \WP_Screen $screen
		 */
		$screen = get_current_screen();
		
		$isOnContentPillarsPage = ($screen && $screen->id 
			=== 'settings_page_abnet-post-stats-content-pillars');
		
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

		if ($isOnContentPillarsPage) {
			wp_enqueue_style(
				'abnet-post-stats-content-pillars',
				ABNET_POST_STATS_PLUGIN_URL . 'assets/css/content-pillars.css',
				array(),
				ABNET_POST_STATS_VERSION
			);
			
			wp_enqueue_script(
				'abnet-post-stats-content-pillars',
				ABNET_POST_STATS_PLUGIN_URL . 'assets/js/content-pillars.js',
				array('jquery'),
				ABNET_POST_STATS_VERSION,
				true
			);
			
			$this->_localizeContentPillarsScript();
		}
	}
	
	/**
	 * Localize script with category data for content pillars page
	 */
	private function _localizeContentPillarsScript(): void {
		/**
		 * @var \WP_Term[] $categories
		 */
		$categories = get_categories(array(
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC'
		));
		
		/**
		 * @var \WP_Term[] $mostUsedCategories
		 */
		$mostUsedCategories = get_categories(array(
			'hide_empty' => false,
			'orderby' => 'count',
			'order' => 'DESC',
			'number' => 10
		));

		$translatedStrings = array(
			'confirmClearAll' => __('Are you sure you want to remove all selected categories?', 'abnet-post-stats')
		);
		
		$localizeData = array(
			'categories' => array_map(function($cat) {
				return array(
					'id' => $cat->term_id,
					'name' => $cat->name,
					'count' => $cat->count
				);
			}, $categories),
			'mostUsedCategories' => array_map(function($cat) {
				return array(
					'id' => $cat->term_id,
					'name' => $cat->name,
					'count' => $cat->count
				);
			}, $mostUsedCategories),
			'strings' => $translatedStrings
		);
		
		wp_localize_script(
			'abnet-post-stats-content-pillars',
			'abnetContentPillars',
			$localizeData
		);
	}
}