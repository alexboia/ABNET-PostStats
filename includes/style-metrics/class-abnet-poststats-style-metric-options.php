<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_StyleMetricOptions {
	private bool $_useAverageSentenceLength = true;

	private bool $_useEntropy = true;

	private bool $_useNegativity = true;

	private bool $_usePunctuation = true;

	/**
	 * @var string[]
	 */
	private array $_negativeWordList = array();

	public static function defaults(): ABNet_PostStats_StyleMetricOptions {
		$defaults = new ABNet_PostStats_StyleMetricOptions();
		return $defaults;
	}

	public static function configured(): ABNet_PostStats_StyleMetricOptions {
		$defaults = self::defaults();
		return $defaults;
	}

	public function getUseAverageSentenceLength(): bool {
		return $this->_useAverageSentenceLength;
	}

	public function getUseEntropy(): bool {
		return $this->_useEntropy;
	}

	public function getUseNegativity(): bool {
		return $this->_useNegativity;
	}

	public function getUsePunctuation(): bool {
		return $this->_usePunctuation;
	}

	public function getNegativeWordList(): array {
		return $this->_negativeWordList;
	}
}