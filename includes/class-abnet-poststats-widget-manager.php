<?php
/**
 * @package ABNet_PostStats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_WidgetManager {
	private const DEFAULT_MONTLY_UPPER_LIMIT = 6;

	private const DEFAULT_YEARLY_UPPER_LIMIT = 5;

	private ABNet_PostStats_DataSource $_dataSource;

	private ABNet_PostStats_ContentPillar_DataSource $_contentPillarDataSource;

	private ABNet_PostStats_View $_view;

	public function __construct(ABNet_PostStats_DataSource $dataSource, 
		ABNet_PostStats_ContentPillar_DataSource $contentPillarDataSource) {
		$this->_dataSource = $dataSource;
		$this->_contentPillarDataSource = $contentPillarDataSource;
		$this->_view = ABNet_PostStats_View::getInstance();
	}

	public function init() {
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
		if ($this->shouldIncludeDashboardWidgets()) {
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

	public function shouldIncludeDashboardWidgets(): bool {
		return is_admin() && $this->_isOnDashboardScreen();
	}

	private function _isOnDashboardScreen(): bool {
		$screen = get_current_screen();
		return $screen && $screen->id === 'dashboard';
	}

	public function renderMonthlyCountsDashboardWidget(): void {
		/**
		 * Filters the number of months shown in the global monthly dashboard widget.
		 *
		 * @param int $count Number of months to include.
		 * @param ABNet_PostStats_ContentPillar|null $pillar Pillar context, null for the global widget.
		 */
		$nMonths = apply_filters('abnet_posts_stats_months_count', 
			self::DEFAULT_MONTLY_UPPER_LIMIT, 
			null);

		if ($nMonths <= 0 || $nMonths > self::DEFAULT_MONTLY_UPPER_LIMIT) {
			$nMonths = self::DEFAULT_MONTLY_UPPER_LIMIT;
		}
		
		$data = $this->_dataSource->getPostCountsPerMonth($nMonths);	
		$this->_renderDashboardWidget($data);
	}

	private function _renderDashboardWidget(ABNet_PostStats_Result $data): void {
		$this->_view->render('dashboard-widget.php', compact('data'));
	}

	public function renderYearlyCountsDashboardWidget(): void {
		/**
		 * Filters the number of years shown in the global yearly dashboard widget.
		 *
		 * @param int $count Number of years to include.
		 * @param ABNet_PostStats_ContentPillar|null $pillar Pillar context, null for the global widget.
		 */
		$nYears = apply_filters('abnet_posts_stats_years_count', 
			self::DEFAULT_YEARLY_UPPER_LIMIT, 
			null);

		if ($nYears <= 0 || $nYears > self::DEFAULT_YEARLY_UPPER_LIMIT) {
			$nYears = self::DEFAULT_YEARLY_UPPER_LIMIT;
		}

		$data = $this->_dataSource->getPostCountsPerYear($nYears);
		$this->_renderDashboardWidget($data);
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

	private function _renderContentPillarMonthlyWidget(ABNet_PostStats_ContentPillar $pillar): void {
		/**
		 * Filters the number of months shown in a content-pillar monthly dashboard widget.
		 *
		 * @param int $count Number of months to include.
		 * @param ABNet_PostStats_ContentPillar $pillar Current content pillar context.
		 */
		$nMonths = apply_filters('abnet_posts_stats_months_count', 
			self::DEFAULT_MONTLY_UPPER_LIMIT, 
			$pillar);
		
		if ($nMonths <= 0 || $nMonths > self::DEFAULT_MONTLY_UPPER_LIMIT) {
			$nMonths = self::DEFAULT_MONTLY_UPPER_LIMIT;
		}
		
		$data = $this->_dataSource->getContentPillarPostCountsPerMonth($pillar, $nMonths);
		$this->_renderDashboardWidget($data);
	}

	private function _renderContentPillarYearlyWidget(ABNet_PostStats_ContentPillar $pillar): void {
		/**
		 * Filters the number of years shown in a content-pillar yearly dashboard widget.
		 *
		 * @param int $count Number of years to include.
		 * @param ABNet_PostStats_ContentPillar $pillar Current content pillar context.
		 */
		$nYears = apply_filters('abnet_posts_stats_years_count', 
			self::DEFAULT_YEARLY_UPPER_LIMIT, 
			$pillar);
		
		if ($nYears <= 0 || $nYears > self::DEFAULT_YEARLY_UPPER_LIMIT) {
			$nYears = self::DEFAULT_YEARLY_UPPER_LIMIT;
		}
		
		$data = $this->_dataSource->getContentPillarPostCountsPerYear($pillar, $nYears);
		$this->_renderDashboardWidget($data);
	}

	public function setDefaultHiddenWidgets(array $hidden, \WP_Screen $screen): array {
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
			// Get all our widget IDs and whether they are initially visible or not
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
}