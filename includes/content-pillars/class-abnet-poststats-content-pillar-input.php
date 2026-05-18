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

class ABNet_PostStats_ContentPillar_Input {
	/**
	 * @var int
	 */
	private $_id = 0;

	/**
	 * @var string
	 */
	private $_name = '';

	/**
	 * @var array
	 */
	private $_categoryIds = array();

	/**
	 * @var string
	 */
	private $_color = ABNET_POST_STATS_DEFAULT_CHART_COLOR;

	/**
	 * @var bool
	 */
	private $_showByDefault = false;

	public static function fromHttpPOST(): ABNet_PostStats_ContentPillar_Input {
		$input = new self();

		$input->_id = max(intval($_POST['pillar_id'] ?? 0), 0);
		$input->_name = sanitize_text_field($_POST['pillar_name'] ?? '');
		$input->_categoryIds = array_map('intval', $_POST['category_ids'] ?? array());
		$input->_color = sanitize_text_field($_POST['pillar_color'] ?? ABNET_POST_STATS_DEFAULT_CHART_COLOR);
		$input->_showByDefault = !empty($_POST['show_by_default']) && $_POST['show_by_default'] === 'yes';
	
		return $input;
	}

	public function getId(): int {
		return $this->_id;
	}

	public function hasId(): bool {
		return $this->_id > 0;
	}

	public function getName(): string {
		return $this->_name;
	}

	public function hasName(): bool {
		return !empty($this->_name);
	}

	public function getCategoryIds(): array {
		return $this->_categoryIds;
	}

	public function hasCategories(): bool {
		return !empty($this->_categoryIds);
	}

	public function getColor(): string {
		return $this->_color;
	}

	public function showByDefault(): bool {
		return $this->_showByDefault;
	}
}
