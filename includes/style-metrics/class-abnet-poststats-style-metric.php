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

class ABNet_PostStats_StyleMetric {
	private string $_key;

	private string $_name;

	private string $_shortDescription;

	private float $_value;

	private string|null $_unit;

	private string $_friendlyRepresentation;

	private ABNet_PostStats_StyleMetricBracket $_bracket;

	public function __construct(string $key, 
		string $name, 
		string $shortDescription, 
		float $value, 
		string|null $unit, 
		string $friendlyRepresentation,  
		ABNet_PostStats_StyleMetricBracket $bracket ) {

		$this->_key = $key;
		$this->_name = $name;
		$this->_shortDescription = $shortDescription;
		$this->_value = $value;
		$this->_unit = $unit;
		$this->_friendlyRepresentation = $friendlyRepresentation;
		$this->_bracket = $bracket;
	}

	public function getKey(): string {
		return $this->_key;
	}

	public function getName(): string {
		return $this->_name;
	}

	public function getShortDescription(): string {
		return $this->_shortDescription;
	}

	public function getValue(): float {
		return $this->_value;
	}

	public function getUnit(): string|null {
		return $this->_unit;
	}

	public function hasUnit(): bool {
		return !empty($this->_unit);
	}

	public function getFriendlyRepresentation(): string {
		return $this->_friendlyRepresentation;
	}

	public function getBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_bracket;
	}

	public function isWithingBracket(): bool {
		return $this->_bracket->containsValue($this->_value);
	}
}