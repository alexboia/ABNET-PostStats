<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_StyleMetricOptions {
	private bool $_useAverageSentenceLength = true;

	private bool $_useEntropy = true;

	private bool $_useNegativity = true;

	private bool $_usePunctuation = true;

	private bool $_useLix = true;

	private bool $_useYulesK = true;

	private bool $_useHapaxToTypes = true;

	/**
	 * @var string[]
	 */
	private array $_negativeWordList = array();

	private int $_yulesKMultiplier = 10000;

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

	public function getUseLix(): bool {
		return $this->_useLix;
	}

	public function getUseYulesK(): bool {
		return $this->_useYulesK;
	}

	public function getUseHapaxToTypes(): bool {
		return $this->_useHapaxToTypes;
	}

	public function getYulesKMultiplier(): int {
		return $this->_yulesKMultiplier;
	}
}