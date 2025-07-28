<?php
/**
 * Plugin Name: ABNet Post Stats
 * Plugin URI: https://github.com/alexboia/ABNET-PostStats
 * Description: A WordPress plugin for displaying post statistics.
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
define('ABNET_DEFAULT_MAX_BAR_HEIGHT', 200);
define('ABNET_DEFAULT_SHOW_TITLE', false);
define('ABNET_DEFAULT_SHOW_SUMMARY', true);

define('ABNET_POST_STATS_INC_DIR', dirname(ABNET_POST_STATS_PLUGIN_FILE) . '/includes/');
define('ABNET_POST_STATS_VIEWS_DIR', dirname(ABNET_POST_STATS_PLUGIN_FILE) . '/views/');

require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-item.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-result.php';
require_once ABNET_POST_STATS_INC_DIR . 'class-abnet-poststats-datasource.php';

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
	 * Get plugin instance
	 */
	public static function getInstance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->_dataSource = new ABNet_PostStats_DataSource();
	}

	public function run(): void {
		add_action('plugins_loaded', array($this, 'init'));
	}
	
	/**
	 * Initialize plugin
	 */
	public function init(): void {
		load_plugin_textdomain('abnet-post-stats', false, dirname(plugin_basename(__FILE__)) . '/languages');
		$this->_initHooks();
	}

	private function _initHooks(): void {
		add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
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
		add_action('wp_dashboard_setup',  array($this, 'onDashboardWidgetsSetup'));
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

	public function enqueueAdminScripts(): void {
		if ($this->_shouldIncludeDashboardWidgets()) {
			wp_enqueue_style(
				'abnet-post-stats-admin',
				ABNET_POST_STATS_PLUGIN_URL . 'assets/css/admin.css',
				array(),
				ABNET_POST_STATS_VERSION
			);
		}
	}
}

ABNet_Post_Stats::getInstance()->run();