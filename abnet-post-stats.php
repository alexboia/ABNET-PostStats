<?php
/**
 * Plugin Name: Simple Post Stats for WordPress
 * Plugin URI: https://github.com/alexboia/ABNET-PostStats
 * Description: A WordPress plugin for displaying simple content creation statistics.
 * Version: 1.0.0
 * Author: Alexandru Boia
 * Author URI: https://alexboia.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: abnet-post-stats
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

// Define plugin constants
define('ABNET_POST_STATS_VERSION', '1.0.0');
define('ABNET_POST_STATS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ABNET_POST_STATS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ABNET_POST_STATS_PLUGIN_FILE', __FILE__);

define('ABNET_POST_STATS_INC_DIR', dirname(ABNET_POST_STATS_PLUGIN_FILE) . '/includes/');
define('ABNET_POST_STATS_VIEWS_DIR', dirname(ABNET_POST_STATS_PLUGIN_FILE) . '/views/');

define('ABNET_POST_STATS_DEFAULT_MAX_BAR_HEIGHT', 200);
define('ABNET_POST_STATS_DEFAULT_SHOW_TITLE', false);
define('ABNET_POST_STATS_DEFAULT_SHOW_SUMMARY', true);
define('ABNET_POST_STATS_DEFAULT_CHART_COLOR', '#005177');

require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-item.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-result.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-datasource.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-db.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-content-pillar.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-content-pillar-datasource.php';

class ABNet_Post_Stats {
	
	/**
	 * @var ABNet_Post_Stats
	 */
	private static $instance = null;

	/**
	 * @var ABNet_PostStats_DataSource
	 */
	private $_dataSource;
	
	/**
	 * @var ABNet_PostStats_ContentPillar_DataSource
	 */
	private $_contentPillarDataSource;

	public static function getInstance(): ABNet_Post_Stats {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->_dataSource = new ABNet_PostStats_DataSource();
		$this->_contentPillarDataSource = new ABNet_PostStats_ContentPillar_DataSource();
	}

	public function run(): void {
		register_activation_hook(ABNET_POST_STATS_PLUGIN_FILE, array($this, 'activate'));
		add_action('plugins_loaded', array($this, 'init'));
	}

	public function activate(): void {
		$this->_createContentPillarsTable();
		$this->_migrateContentPillarsTable();
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

		$this->registerDashboardWidgets();
	}

	private function _shouldIncludeDashboardWidgets(): bool {
		return is_admin() && $this->_isOnDashboardScreen();
	}

	private function _isOnDashboardScreen(): bool {
		$screen = get_current_screen();
		return $screen && $screen->id === 'dashboard';
	}
	
	public function registerDashboardWidgets(): void {
		add_action('updated_user_meta', 
			array($this, 'onUserUpdateHiddenDashboardWidgets'), 
			10, 
			4);

		add_filter('hidden_meta_boxes', 
			array($this, 'setDefaultHiddenWidgets'), 
			10, 
			2);

		add_action('wp_dashboard_setup',  
			array($this, 'onDashboardWidgetsSetup'));
	}

	public function onDashboardWidgetsSetup(): void {
		if ($this->_shouldIncludeDashboardWidgets()) {
			wp_add_dashboard_widget(
				'abnet_monthly_post_stats_widget',
				__('Post Statistics per Month', 'abnet-post-stats'),
				array($this, 'renderMonthlyCountsDashboardWidget')
			);

			wp_add_dashboard_widget(
				'abnet_yearly_post_stats_widget',
				__('Post Statistics per Year', 'abnet-post-stats'),
				array($this, 'renderYearlyCountsDashboardWidget')
			);
			
			// Add content pillar widgets
			$this->_addContentPillarDashboardWidgets();
		}
	}

	private function _addContentPillarDashboardWidgets(): void {
		$contentPillars = $this->_contentPillarDataSource->getAllContentPillars();
		
		foreach ($contentPillars as $pillar) {
			// Monthly widget
			wp_add_dashboard_widget(
				'abnet_pillar_monthly_' . $pillar->getId(),
				sprintf(__('Posts in %s (Monthly)', 'abnet-post-stats'), $pillar->getName()),
				function() use ($pillar) {
					$this->_renderContentPillarMonthlyWidget($pillar);
				}
			);
			
			// Yearly widget
			wp_add_dashboard_widget(
				'abnet_pillar_yearly_' . $pillar->getId(),
				sprintf(__('Posts in %s (Yearly)', 'abnet-post-stats'), $pillar->getName()),
				function() use ($pillar) {
					$this->_renderContentPillarYearlyWidget($pillar);
				}
			);
		}
	}

	public function renderMonthlyCountsDashboardWidget(): void {
		$nMonths = apply_filters('abnet_posts_stats_months_count', 5);
		if ($nMonths <= 0 || $nMonths > 6) {
			$nMonths = 6;
		}
		
		$data = $this->_dataSource->getPostCountsPerMonth($nMonths);	
		$this->_renderDashboardWidget($data);
	}

	private function _renderDashboardWidget(ABNet_Post_Stats_Result $data): void {
		require ABNET_POST_STATS_VIEWS_DIR . '/dashboard-widget.php';
	}

	public function renderYearlyCountsDashboardWidget(): void {
		$nYears = apply_filters('abnet_posts_stats_years_count', 5);
		if ($nYears <= 0 || $nYears > 5) {
			$nYears = 5;
		}

		$data = $this->_dataSource->getPostCountsPerYear($nYears);
		$this->_renderDashboardWidget($data);
	}

	private function _renderContentPillarMonthlyWidget(ABNet_Post_Stats_Content_Pillar $pillar): void {
		$nMonths = apply_filters('abnet_posts_stats_months_count', 5, $pillar->getId(), $pillar->getCategoryIds());
		if ($nMonths <= 0 || $nMonths > 6) {
			$nMonths = 6;
		}
		
		$data = $this->_dataSource->getContentPillarPostCountsPerMonth($pillar, $nMonths);
		$this->_renderDashboardWidget($data);
	}

	private function _renderContentPillarYearlyWidget(ABNet_Post_Stats_Content_Pillar $pillar): void {
		$nYears = apply_filters('abnet_posts_stats_years_count', 5, $pillar->getId(), $pillar->getCategoryIds());
		if ($nYears <= 0 || $nYears > 5) {
			$nYears = 5;
		}
		
		$data = $this->_dataSource->getContentPillarPostCountsPerYear($pillar, $nYears);
		$this->_renderDashboardWidget($data);
	}

	public function setDefaultHiddenWidgets(array $hidden, $screen): array {
		// Only modify if we're on the dashboard screen
		if ($screen && $screen->id === 'dashboard') {
			$hidden = $hidden ?? array();
			$ourWidgets = $this->_getOurWidgetsInfo();
			
			// Check current user's dashboard widget preferences
			$currentUserId = get_current_user_id();
			$userShownWidgets = get_user_meta($currentUserId, 
				'abnet_dashboard_widgets_explicitly_shown', 
				true);
		
			if (empty($userShownWidgets) || !is_array($userShownWidgets)) {
				$userShownWidgets = array();
			}

			foreach ($ourWidgets as $id => $defaultShow) {
				$shownByUser = in_array($id, $userShownWidgets);
				if (!$defaultShow && !$shownByUser && !in_array($id, $hidden)) {
					$hidden[] = $id;
				}
			}
		}

		return $hidden;
	}

	public function onUserUpdateHiddenDashboardWidgets($metaId, $userId, $key, $hiddenWidgets) {
		var_dump($key);
		if ($key != 'metaboxhidden_dashboard') {
			return;
		}

		$ourWidgets = $this->_getOurWidgetsInfo();
		$ourWidgetsIds = array_keys($ourWidgets);

		$explicitlyShown = array();
		if (!empty($hiddenWidgets)) {
			foreach ($ourWidgetsIds as $widgetId) {
				if (!in_array($widgetId, $hiddenWidgets)) {
					$explicitlyShown[] = $widgetId;
				}
			}
		} else {
			$explicitlyShown = $ourWidgetsIds;
		}

		update_user_meta($userId, 
			'abnet_dashboard_widgets_explicitly_shown', 
			$explicitlyShown);
	}

	private function _getOurWidgetsInfo(): array {
		static $ourWidgets = null;

		if ($ourWidgets === null) {
			// Get all our widget IDs
			$ourWidgets = array(
				'abnet_monthly_post_stats_widget' => false,
				'abnet_yearly_post_stats_widget' => false
			);
			
			// Add content pillar widget IDs
			$contentPillars = $this->_contentPillarDataSource->getAllContentPillars();
			foreach ($contentPillars as $pillar) {
				$id = $pillar->getId();
				$showByDefault = $pillar->showByDefault();
				$ourWidgets['abnet_pillar_monthly_' . $id] = $showByDefault;
				$ourWidgets['abnet_pillar_yearly_' . $id] = $showByDefault;
			}
		}
		

		return $ourWidgets;
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
		if ($_SERVER['REQUEST_METHOD'] === 'POST' 
			&& isset($_POST['abnet_content_pillar_nonce'])) {
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
		$nonce = isset($_POST['abnet_content_pillar_nonce']) 
			? $_POST['abnet_content_pillar_nonce']
			: '';

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
	 * Process create content pillar form
	 * 
	 * @return array Array with 'message' and 'type' keys
	 */
	private function _processCreateContentPillar(): array {
		$name = sanitize_text_field($_POST['pillar_name'] ?? '');
		$categoryIds = array_map('intval', $_POST['category_ids'] ?? array());
		$color = sanitize_text_field($_POST['pillar_color'] ?? '#3498db');
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
		$color = sanitize_text_field($_POST['pillar_color'] ?? '#3498db');
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
		$screen = get_current_screen();
		
		if ($this->_shouldIncludeDashboardWidgets()) {
			wp_enqueue_style(
				'abnet-post-stats-admin',
				ABNET_POST_STATS_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				ABNET_POST_STATS_VERSION
			);
		}
		
		// Enqueue styles for content pillars admin page
		if ($screen && $screen->id === 'settings_page_abnet-post-stats-content-pillars') {
			wp_enqueue_style(
				'abnet-post-stats-admin',
				ABNET_POST_STATS_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				ABNET_POST_STATS_VERSION
			);
			
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
			
			// Localize script with category data
			$this->_localizeContentPillarsScript();
		}
	}
	
	/**
	 * Localize script with category data for content pillars page
	 */
	private function _localizeContentPillarsScript(): void {
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
			'strings' => array(
				'confirmClearAll' => __('Are you sure you want to remove all selected categories?', 'abnet-post-stats')
			)
		);
		
		wp_localize_script(
			'abnet-post-stats-content-pillars',
			'abnetContentPillars',
			$localizeData
		);
	}
}

ABNet_Post_Stats::getInstance()->run();