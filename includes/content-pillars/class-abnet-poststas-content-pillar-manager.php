<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_ContentPillar_Manager {
	private ABNet_PostStats_ContentPillar_DataSource $_contentPillarDataSource;

	private ABNet_PostStats_View $_view;

	public function __construct() {
		$this->_view = ABNet_PostStats_View::getInstance();
		$this->_contentPillarDataSource = new ABNet_PostStats_ContentPillar_DataSource();
	}

	public function isOnContentPillarsPage(): bool {
		/**
		 * @var \WP_Screen $screen
		 */
		$screen = get_current_screen();
		return ($screen && $screen->id === 'settings_page_abnet-post-stats-content-pillars');
	}

	public function setupScriptsAndStyles(): bool {
		$isOnContentPillarsPage = $this->isOnContentPillarsPage();

		if ($isOnContentPillarsPage) {
			wp_enqueue_style(
				'abnet-post-stats-content-pillars-styles',
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

		return $isOnContentPillarsPage;
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

		$translatedStrings = $this->_getTranslatedStrings();
		
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

	private function _getTranslatedStrings() {
		return  array(
			'confirmClearAll' 
				=> __('Are you sure you want to remove all selected categories?', 'abnet-post-stats'),
			'mostUsedCategoriesTitle' 
				=> __('Most Used Categories', 'abnet-post-stats')
		);
	}

	public function setupMenu(): void {
		$optionsTitle = __('Condei Simple Post Stats - Content Pillars Definitions', 
			'abnet-post-stats');

		add_options_page(
			$optionsTitle,
			$optionsTitle,
			'manage_options',
			'abnet-post-stats-content-pillars',
			array($this, 'renderContentPillarsPage')
		);
	}

	public function renderContentPillarsPage(): void {
		$message = '';
		$messageType = '';
		
		// Process form submissions
		if ($this->_isContentPillarFormSubmitted()) {
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
		$editingPillar = $this->_getContentPillarToEditFromHttpGet();

		$this->_view->render('admin-content-pillars.php', compact(
			'message',
			'messageType',
			'contentPillars',
			'categories',
			'mostUsedCategories',
			'editingPillar'
		));
	}

	private function _isContentPillarFormSubmitted(): bool {
		/* phpcs:disable WordPress.Security.ValidatedSanitizedInput -- Variable explicitly checked against valid values. */
		/* phpcs:disable WordPress.Security.NonceVerification -- False positive: No form processing is carried out here. */
		
		return !empty($_SERVER['REQUEST_METHOD']) 
			&& strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' 
			&& isset($_POST['abnet_content_pillar_nonce']);

		/* phpcs:disable WordPress.Security.NonceVerification */
		/* phpcs:enable WordPress.Security.ValidatedSanitizedInput */
	}

	private function _getContentPillarToEditFromHttpGet(): ?ABNet_PostStats_ContentPillar {
		/* phpcs:disable WordPress.Security.NonceVerification -- False positive: No form processing is carried out here. */

		$editingPillar = null;
		if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
			$editPillarId = intval($_GET['edit']);
			if ($editPillarId > 0) {
				$editingPillar = $this->_contentPillarDataSource->getContentPillarById($editPillarId);
			}
		}

		/* phpcs:enable WordPress.Security.NonceVerification */

		return $editingPillar;
	}

	private function _processContentPillarForm(): array {
		$nonce = sanitize_text_field(wp_unslash($_POST['abnet_content_pillar_nonce'] ?? ''));
		if (!wp_verify_nonce($nonce, 'abnet_content_pillar_action')) {
			return array(
				'message' => __('Security check failed.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		$action = sanitize_text_field(wp_unslash($_POST['action'] ?? ''));
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
		$input = ABNet_PostStats_ContentPillar_Input::fromHttpPOST();
		
		if (!$input->hasName()) {
			return array(
				'message' => __('Content pillar name is required.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if (!$input->hasCategories()) {
			return array(
				'message' => __('At least one category must be selected.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if ($this->_contentPillarDataSource->contentPillarNameExists($input->getName())) {
			return array(
				'message' => __('A content pillar with this name already exists.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		$result = $this->_contentPillarDataSource->createContentPillar(
			$input->getName(), 
			$input->getCategoryIds(), 
			$input->getColor(), 
			$input->showByDefault()
		);

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
		$input = ABNet_PostStats_ContentPillar_Input::fromHttpPOST();
		
		if (!$input->hasName()) {
			return array(
				'message' => __('Content pillar name is required.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if (!$input->hasCategories()) {
			return array(
				'message' => __('At least one category must be selected.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		if ($this->_contentPillarDataSource->contentPillarNameExists($input->getName(), $input->getId())) {
			return array(				
				'message' => __('A content pillar with this name already exists.', 'abnet-post-stats'),
				'type' => 'error'
			);
		}
		
		$result = $this->_contentPillarDataSource->updateContentPillar(
			$input->getId(), 
			$input->getName(), 
			$input->getCategoryIds(), 
			$input->getColor(), 
			$input->showByDefault()
		);
		
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
		/* phpcs:disable WordPress.Security.NonceVerification -- False positive: Nonce validation is carried out by caller method. */
		
		$id = intval($_POST['pillar_id'] ?? 0);
		$result = $this->_contentPillarDataSource->deleteContentPillar($id);

		/* phpcs:enable WordPress.Security.NonceVerification */
		
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
}