<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

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

	public function __construct(string $key, 
		string $name, 
		string $shortDescription, 
		float $value, 
		string|null $unit, 
		string $friendlyRepresentation) {

		$this->_key = $key;
		$this->_name = $name;
		$this->_shortDescription = $shortDescription;
		$this->_value = $value;
		$this->_unit = $unit;
		$this->_friendlyRepresentation = $friendlyRepresentation;
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

	public function getFriendlyRepresentation(): string {
		return $this->_friendlyRepresentation;
	}
}