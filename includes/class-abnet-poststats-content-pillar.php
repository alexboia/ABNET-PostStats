<?php
/**
 * Represents a content pillar
 * 
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_Post_Stats_Content_Pillar {
	private int $_id;

	private string $_name;

	private array $_categoryIds;

	private string $_color;

	private bool $_showByDefault;

	private string $_createdAt;

	private string $_updatedAt;
	
	public function __construct(array $dbRow) {
		$this->_id = !empty($dbRow['content_pillar_id']) ?
			intval($dbRow['content_pillar_id']) 
			: 0;
		
		$this->_name = !empty($dbRow['content_pillar_name']) 
			? $dbRow['content_pillar_name'] 
			: '';

		$this->_color = !empty($dbRow['content_pillar_color']) 
			? $dbRow['content_pillar_color'] 
			: ABNET_POST_STATS_DEFAULT_CHART_COLOR;

		$this->_createdAt = !empty($dbRow['created_at']) ? $dbRow['created_at'] : '';
		$this->_updatedAt = !empty($dbRow['updated_at']) ? $dbRow['updated_at'] : '';
		
		$this->_showByDefault = isset($dbRow['show_by_default']) 
			? intval($dbRow['show_by_default']) === 1 
			: true;
		
		// Parse JSON definition
		$definition = !empty($dbRow['content_pillar_definition']) 
			? $dbRow['content_pillar_definition'] 
			: '{}';

		$decodedDefinition = json_decode($definition, true);
		
		$this->_categoryIds = !empty($decodedDefinition['categories']) && is_array($decodedDefinition['categories']) 
			? array_unique(array_map('intval', $decodedDefinition['categories']))
			: array();
	}
	
	/**
	 * @return ABNet_Post_Stats_Content_Pillar[]
	 */
	public static function fromResultList(array $dbRows): array {
		$pillars = array();
		foreach ($dbRows as $dbRow) {
			$pillars[] = new self($dbRow);
		}
		return $pillars;
	}
	
	public function getId(): int {
		return $this->_id;
	}
	
	public function getName(): string {
		return $this->_name;
	}
	
	public function getCategoryIds(): array {
		return $this->_categoryIds;
	}
	
	public function getColor(): string {
		return $this->_color;
	}
	
	public function getCreatedAt(): string {
		return $this->_createdAt;
	}
	
	public function getUpdatedAt(): string {
		return $this->_updatedAt;
	}
	
	public function hasCategoryIds(): bool {
		return !empty($this->_categoryIds);
	}
	
	/**
	 * Get the definition as JSON string
	 */
	public function getDefinitionJson(): string {
		return json_encode(array(
			'categories' => $this->_categoryIds
		));
	}

	public function showByDefault(): bool {
		return $this->_showByDefault;
	}
}
