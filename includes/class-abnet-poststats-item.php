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

	private string $_barColor;

	public function __construct(int $value, string $label, string $barColor) {	
		$this->_value = $value;
		$this->_label = $label;
		$this->_barColor = $barColor;
	}

	public function getValue(): int {	
		return $this->_value;
	}			

	public function hasValue(): bool {
		return $this->_value > 0;
	}

	public function getLabel(): string {
		return $this->_label;
	}

	public function getBarColor(): string {
		return $this->_barColor;
	}
}