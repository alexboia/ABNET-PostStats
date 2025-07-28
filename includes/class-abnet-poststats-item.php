<?php
/**
 * Represents a single item in the post statistics data
 * 
 * @package ABNet_Post_Stats_Item
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ABNet_Post_Stats_Item {
	private int $_value;

	private string $_label;

	public function __construct(int $value, string $label) {	
		$this->_value = $value;
		$this->_label = $label;
	}

	public function getValue(): int {	
		return $this->_value;
	}			

	public function getLabel(): string {
		return $this->_label;
	}
}