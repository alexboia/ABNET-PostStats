<?php
/**
 * Represents a single result set of post statistics
 * 
 * @package ABNet_Post_Stats_Result
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ABNet_Post_Stats_Result {
	/**
	 * @var ABNet_Post_Stats_Item[]
	 */
	private array $_items;

	private string $_title;
	
	private int $_count;

	/**
	 * @var int[]
	 */
	private array $_values;

	private int $_sumOfValues;	

	public function __construct(string $title, array $items) {
		$this->_title = $title;
		$this->_items = $items;
		$this->_count = count($items);

		$this->_values = array_map(function($item) {
			return $item->getValue();
		}, $items);

		$this->_sumOfValues = array_sum($this->_values);
	}
	
	//generate getters for fields
	public function getItems(): array {
		return $this->_items;
	}

	public function getTitle(): string {
		return $this->_title;
	}

	public function getCount(): int {
		return $this->_count;
	}

	public function hasItems() : bool {
		return !empty($this->_items);
	}

	public function getValues(): array {
		return $this->_values;
	}

	public function getSumOfValues(): int {
		return $this->_sumOfValues;
	}

	public function getMaxValue(): int {
		return !empty($this->_values) 
			? max($this->_values) 
			: 0;
	}

	public function getAverageValue(): float {
		return !empty($this->_values) 
			? round($this->_sumOfValues / count($this->_values), 1) 
			: 0;
	}
}