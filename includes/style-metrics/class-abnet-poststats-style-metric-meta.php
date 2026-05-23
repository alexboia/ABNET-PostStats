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

class ABNet_PostStats_StyleMetric_Meta {
	private string $_name;

	private string $_description;

	public function __construct(string $name, string $description){
		$this->_name = $name;
		$this->_description = $description;
	}

	public function getName(): string {
		return $this->_name;
	}

	public function getDescription(): string {
		return $this->_description;
	}
}