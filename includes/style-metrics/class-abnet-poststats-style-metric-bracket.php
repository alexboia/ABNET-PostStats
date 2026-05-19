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

 class ABNet_PostStats_StyleMetricBracket {
	private $_min = PHP_FLOAT_MIN;

	private $_max =  PHP_FLOAT_MAX;

	public function __construct(float $min, float $max){
		$this->_min = $min;
		$this->_max = $max;
	}

	public static function unbounded(): ABNet_PostStats_StyleMetricBracket {
		return new self(PHP_FLOAT_MIN, PHP_FLOAT_MAX);
	}

	public static function fromArray(array $source): ABNet_PostStats_StyleMetricBracket {
		$min = !empty($source['min']) 
			? floatval($source['min']) 
			: PHP_FLOAT_MIN;

		$max = !empty($source['max']) 
			? floatval($source['max']) 
			: PHP_FLOAT_MAX;

		return new self($min, $max);
	}

	public function isBounded(): bool {
		return $this->_min > PHP_FLOAT_MIN && $this->_max < PHP_FLOAT_MAX;
	}

	public function isUnbounded(): bool {
		return !$this->isBounded();
	}

	public function toArray(): array {
		return array(
			'min' => $this->_min,
			'max' => $this->_max,
			'unbounded' => $this->isUnbounded()
		);
	}

	public function containsValue(float $value): bool {
		return $this->_min <= $value && $this->_max >= $value;
	}

	public function getMin(): float {
		return $this->_min;
	}

	public function getMax(): float {
		return $this->_max;
	}
 }