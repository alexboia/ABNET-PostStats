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

class ABNet_PostStats_StyleMetricOptions {
	public const OPTION_NAME = 'abnet_post_stats_style_metric_options';

	public const KEY_USE_AVERAGE_SENTENCE_LENGTH = 'use_average_sentence_length';

	public const KEY_USE_ENTROPY = 'use_entropy';

	public const KEY_USE_NEGATIVITY = 'use_negativity';

	public const KEY_USE_PUNCTUATION = 'use_punctuation';

	public const KEY_USE_LIX = 'use_lix';

	public const KEY_USE_YULES_K = 'use_yules_k';

	public const KEY_USE_HAPAX_TO_TYPES = 'use_hapax_to_types';

	public const KEY_NEGATIVE_WORD_LIST = 'negative_word_list';

	public const KEY_YULES_K_MULTIPLIER = 'yules_k_multiplier';

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
		$storedOptions = get_option(self::OPTION_NAME, array());
		if (!is_array($storedOptions)) {
			$storedOptions = array();
		}

		return self::fromArray($storedOptions);
	}

	public static function fromArray(array $rawOptions): ABNet_PostStats_StyleMetricOptions {
		$defaults = self::defaults();
		$sanitized = self::sanitizeRawOptionsArray($rawOptions);

		$options = new ABNet_PostStats_StyleMetricOptions();
		$options->_useAverageSentenceLength = $sanitized[self::KEY_USE_AVERAGE_SENTENCE_LENGTH] 
			?? $defaults->_useAverageSentenceLength;
		$options->_useEntropy = $sanitized[self::KEY_USE_ENTROPY] 
			?? $defaults->_useEntropy;
		$options->_useNegativity = $sanitized[self::KEY_USE_NEGATIVITY] 
			?? $defaults->_useNegativity;
		$options->_usePunctuation = $sanitized[self::KEY_USE_PUNCTUATION] 
			?? $defaults->_usePunctuation;
		$options->_useLix = $sanitized[self::KEY_USE_LIX] 
			?? $defaults->_useLix;
		$options->_useYulesK = $sanitized[self::KEY_USE_YULES_K] 
			?? $defaults->_useYulesK;
		$options->_useHapaxToTypes = $sanitized[self::KEY_USE_HAPAX_TO_TYPES] 
			?? $defaults->_useHapaxToTypes;
		$options->_negativeWordList = $sanitized[self::KEY_NEGATIVE_WORD_LIST] 
			?? $defaults->_negativeWordList;
		$options->_yulesKMultiplier = $sanitized[self::KEY_YULES_K_MULTIPLIER] 
			?? $defaults->_yulesKMultiplier;

		return $options;
	}

	public static function sanitizeRawOptionsArray(array $rawOptions): array {
		$defaults = self::defaults();
		$rawOptions = is_array($rawOptions) 
			? $rawOptions 
			: array();

		$negativeWordListRaw = self::_normalizeNegativeWordList($rawOptions, 
			$defaults->_negativeWordList);

		$negativeWordList = self::_cleanRawNegativeWordList($negativeWordListRaw);

		$yulesKMultiplier = isset($rawOptions[self::KEY_YULES_K_MULTIPLIER])
			? (int) $rawOptions[self::KEY_YULES_K_MULTIPLIER]
			: $defaults->_yulesKMultiplier;

		if ($yulesKMultiplier <= 0) {
			$yulesKMultiplier = $defaults->_yulesKMultiplier;
		}

		return array(
			self::KEY_USE_AVERAGE_SENTENCE_LENGTH => isset($rawOptions[self::KEY_USE_AVERAGE_SENTENCE_LENGTH]) 
				? !empty($rawOptions[self::KEY_USE_AVERAGE_SENTENCE_LENGTH])
				: $defaults->_useAverageSentenceLength,

			self::KEY_USE_ENTROPY => isset($rawOptions[self::KEY_USE_ENTROPY])
				? !empty($rawOptions[self::KEY_USE_ENTROPY])
				: $defaults->_useEntropy,

			self::KEY_USE_NEGATIVITY => isset($rawOptions[self::KEY_USE_NEGATIVITY]) 
				? !empty($rawOptions[self::KEY_USE_NEGATIVITY])
				: $defaults->_useNegativity,

			self::KEY_USE_PUNCTUATION => isset($rawOptions[self::KEY_USE_PUNCTUATION])
				? !empty($rawOptions[self::KEY_USE_PUNCTUATION])
				: $defaults->_usePunctuation,

			self::KEY_USE_LIX => isset($rawOptions[self::KEY_USE_LIX]) 
				? !empty($rawOptions[self::KEY_USE_LIX])
				: $defaults->_useLix,

			self::KEY_USE_YULES_K => isset($rawOptions[self::KEY_USE_YULES_K]) 
				? !empty($rawOptions[self::KEY_USE_YULES_K])
				: $defaults->_useYulesK,

			self::KEY_USE_HAPAX_TO_TYPES => isset($rawOptions[self::KEY_USE_HAPAX_TO_TYPES])
				? !empty($rawOptions[self::KEY_USE_HAPAX_TO_TYPES])
				: $defaults->_useHapaxToTypes,

			self::KEY_NEGATIVE_WORD_LIST => $negativeWordList,
			self::KEY_YULES_K_MULTIPLIER => $yulesKMultiplier
		);
	}

	private static function _normalizeNegativeWordList(array $rawOptions, array $defaultNegativeWordList): array {
		if (!empty($rawOptions[self::KEY_NEGATIVE_WORD_LIST])) {
			if (!is_array($rawOptions[self::KEY_NEGATIVE_WORD_LIST])) {
				$rawOptions[self::KEY_NEGATIVE_WORD_LIST] = preg_split(	
					'/\r\n|\r|\n/', 
					$rawOptions[self::KEY_NEGATIVE_WORD_LIST]
				);
			}

			$negativeWordListRaw = $rawOptions[self::KEY_NEGATIVE_WORD_LIST];
		} else {
			$negativeWordListRaw = $defaultNegativeWordList;
		}

		return $negativeWordListRaw;
	}

	private static function _cleanRawNegativeWordList(array $negativeWordListRaw): array {
		return array_values(
			array_filter(
				array_map(
					'sanitize_text_field', 
					array_map(function($rawWord) {
						return trim(trim($rawWord), ',.;:');
					}, $negativeWordListRaw)
				), 
				function($word) {
					return !empty($word) || $word === 0 || $word === '0';
				}
			)
		);
	}

	public function toArray(): array {
		return array(
			self::KEY_USE_AVERAGE_SENTENCE_LENGTH => $this->_useAverageSentenceLength,
			self::KEY_USE_ENTROPY => $this->_useEntropy,
			self::KEY_USE_NEGATIVITY => $this->_useNegativity,
			self::KEY_USE_PUNCTUATION => $this->_usePunctuation,
			self::KEY_USE_LIX => $this->_useLix,
			self::KEY_USE_YULES_K => $this->_useYulesK,
			self::KEY_USE_HAPAX_TO_TYPES => $this->_useHapaxToTypes,
			self::KEY_NEGATIVE_WORD_LIST => $this->_negativeWordList,
			self::KEY_YULES_K_MULTIPLIER => $this->_yulesKMultiplier
		);
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