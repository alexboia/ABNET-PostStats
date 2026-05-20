<?php
/**
 * @package ABNet_PostStats
 * @since 1.1.0
 */

declare(strict_types=1);

use PhpParser\Node\Expr\StaticCall;

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

	public const KEY_AVERAGE_SENTENCE_LENGTH_BRACKET = 'average_sentence_length_bracket';

	public const KEY_ENTROPY_BRACKET = 'entropy_bracket';

	public const KEY_NEGATIVITY_BRACKET = 'negativity_bracket';

	public const KEY_PUNCTUATION_BRACKET = 'punctuation_bracket';

	public const KEY_LIX_BRACKET = 'lix_bracket';

	public const KEY_YULES_K_BRACKET = 'yules_k_bracket';

	public const KEY_HAPAX_TO_TYPES_BRACKET = 'hapax_to_types_bracket';

	private bool $_useAverageSentenceLength = true;

	private bool $_useEntropy = true;

	private bool $_useNegativity = true;

	private bool $_usePunctuation = true;

	private bool $_useLix = true;

	private bool $_useYulesK = true;

	private bool $_useHapaxToTypes = true;

	private ABNet_PostStats_StyleMetricBracket $_averageSentenceLengthBracket;

	private ABNet_PostStats_StyleMetricBracket $_entryopyBracket;

	private ABNet_PostStats_StyleMetricBracket $_yulesKBracket;

	private ABNet_PostStats_StyleMetricBracket $_negativityBracket;

	private ABNet_PostStats_StyleMetricBracket $_punctuationBracket;

	private ABNet_PostStats_StyleMetricBracket $_lixBracket;

	private ABNet_PostStats_StyleMetricBracket $_hapaxBracket;

	private ?array $_asArray = null;

	/**
	 * @var string[]
	 */
	private array $_negativeWordList = array();

	private int $_yulesKMultiplier = 10000;

	public function __construct(){
		$this->_averageSentenceLengthBracket = new ABNet_PostStats_StyleMetricBracket(14, 21);
		$this->_entryopyBracket = new ABNet_PostStats_StyleMetricBracket(4, 6);
		$this->_yulesKBracket = new ABNet_PostStats_StyleMetricBracket(45, 68);
		$this->_negativityBracket = new ABNet_PostStats_StyleMetricBracket(10, 25);
		$this->_punctuationBracket = new ABNet_PostStats_StyleMetricBracket(20, 40);
		$this->_lixBracket = new ABNet_PostStats_StyleMetricBracket(42, 56);
		$this->_hapaxBracket = new ABNet_PostStats_StyleMetricBracket(55, 75);
		$this->_negativeWordList = ABNet_PostStats_StyleMetricNegativityProvider::getDefaultNegativeWordListEn();
	}

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
		$options->_averageSentenceLengthBracket = ABNet_PostStats_StyleMetricBracket::fromArray(
			$sanitized[self::KEY_AVERAGE_SENTENCE_LENGTH_BRACKET]
				?? $defaults->_averageSentenceLengthBracket->toArray()
		);
		$options->_entryopyBracket = ABNet_PostStats_StyleMetricBracket::fromArray(
			$sanitized[self::KEY_ENTROPY_BRACKET]
				?? $defaults->_entryopyBracket->toArray()
		);
		$options->_negativityBracket = ABNet_PostStats_StyleMetricBracket::fromArray(
			$sanitized[self::KEY_NEGATIVITY_BRACKET]
				?? $defaults->_negativityBracket->toArray()
		);
		$options->_punctuationBracket = ABNet_PostStats_StyleMetricBracket::fromArray(
			$sanitized[self::KEY_PUNCTUATION_BRACKET]
				?? $defaults->_punctuationBracket->toArray()
		);
		$options->_lixBracket = ABNet_PostStats_StyleMetricBracket::fromArray(
			$sanitized[self::KEY_LIX_BRACKET]
				?? $defaults->_lixBracket->toArray()
		);
		$options->_yulesKBracket = ABNet_PostStats_StyleMetricBracket::fromArray(
			$sanitized[self::KEY_YULES_K_BRACKET]
				?? $defaults->_yulesKBracket->toArray()
		);
		$options->_hapaxBracket = ABNet_PostStats_StyleMetricBracket::fromArray(
			$sanitized[self::KEY_HAPAX_TO_TYPES_BRACKET]
				?? $defaults->_hapaxBracket->toArray()
		);

		return $options;
	}

	public static function sanitizeRawOptionsInputArray(array $rawOptions): array {
		foreach (self::_getOptionToggleKeys() as $key) {
			if (!isset($rawOptions[$key])) {
				$rawOptions[$key] = false;
			}
		}
	
		return self::sanitizeRawOptionsArray($rawOptions);
	}	

	private static function _getOptionToggleKeys(): array {
		return array_values(self::getProviderOptionToggleKeyMapping());
	}

	public static function getProviderOptionToggleKeyMapping(): array {
		return array(
			ABNet_PostStats_StyleMetricAverageSentenceLengthProvider::KEY 
				=> self::KEY_USE_AVERAGE_SENTENCE_LENGTH,
			ABNet_PostStats_StyleMetricEntropyProvider::KEY 
				=> self::KEY_USE_ENTROPY,
			ABNet_PostStats_StyleMetricNegativityProvider::KEY
				=> self::KEY_USE_NEGATIVITY,
			ABNet_PostStats_StyleMetricPunctuationProvider::KEY
				=> self::KEY_USE_PUNCTUATION,
			ABNet_PostStats_StyleMetricLixProvider::KEY
				=> self::KEY_USE_LIX,
			ABNet_PostStats_StyleMetricYulesKProvider::KEY
				=> self::KEY_USE_YULES_K,
			ABNet_PostStats_StyleMetricHapaxToTypesProvider::KEY
				=> self::KEY_USE_HAPAX_TO_TYPES
		);
	}

	public static function getProviderBracketOptionKeyMapping(): array {
		return array(
			ABNet_PostStats_StyleMetricAverageSentenceLengthProvider::KEY
				=> ABNet_PostStats_StyleMetricOptions::KEY_AVERAGE_SENTENCE_LENGTH_BRACKET,
			ABNet_PostStats_StyleMetricEntropyProvider::KEY
				=> ABNet_PostStats_StyleMetricOptions::KEY_ENTROPY_BRACKET,
			ABNet_PostStats_StyleMetricNegativityProvider::KEY
				=> ABNet_PostStats_StyleMetricOptions::KEY_NEGATIVITY_BRACKET,
			ABNet_PostStats_StyleMetricPunctuationProvider::KEY
				=> ABNet_PostStats_StyleMetricOptions::KEY_PUNCTUATION_BRACKET,
			ABNet_PostStats_StyleMetricLixProvider::KEY
				=> ABNet_PostStats_StyleMetricOptions::KEY_LIX_BRACKET,
			ABNet_PostStats_StyleMetricYulesKProvider::KEY
				=> ABNet_PostStats_StyleMetricOptions::KEY_YULES_K_BRACKET,
			ABNet_PostStats_StyleMetricHapaxToTypesProvider::KEY
				=> ABNet_PostStats_StyleMetricOptions::KEY_HAPAX_TO_TYPES_BRACKET
		);
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

		$averageSentenceLengthBracket = self::_sanitizeRawBracket(
			$rawOptions,
			self::KEY_AVERAGE_SENTENCE_LENGTH_BRACKET,
			$defaults->_averageSentenceLengthBracket
		);
		$entropyBracket = self::_sanitizeRawBracket(
			$rawOptions,
			self::KEY_ENTROPY_BRACKET,
			$defaults->_entryopyBracket
		);
		$negativityBracket = self::_sanitizeRawBracket(
			$rawOptions,
			self::KEY_NEGATIVITY_BRACKET,
			$defaults->_negativityBracket
		);
		$punctuationBracket = self::_sanitizeRawBracket(
			$rawOptions,
			self::KEY_PUNCTUATION_BRACKET,
			$defaults->_punctuationBracket
		);
		$lixBracket = self::_sanitizeRawBracket(
			$rawOptions,
			self::KEY_LIX_BRACKET,
			$defaults->_lixBracket
		);
		$yulesKBracket = self::_sanitizeRawBracket(
			$rawOptions,
			self::KEY_YULES_K_BRACKET,
			$defaults->_yulesKBracket
		);
		$hapaxToTypesBracket = self::_sanitizeRawBracket(
			$rawOptions,
			self::KEY_HAPAX_TO_TYPES_BRACKET,
			$defaults->_hapaxBracket
		);

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
			self::KEY_YULES_K_MULTIPLIER => $yulesKMultiplier,
			self::KEY_AVERAGE_SENTENCE_LENGTH_BRACKET => $averageSentenceLengthBracket,
			self::KEY_ENTROPY_BRACKET => $entropyBracket,
			self::KEY_NEGATIVITY_BRACKET => $negativityBracket,
			self::KEY_PUNCTUATION_BRACKET => $punctuationBracket,
			self::KEY_LIX_BRACKET => $lixBracket,
			self::KEY_YULES_K_BRACKET => $yulesKBracket,
			self::KEY_HAPAX_TO_TYPES_BRACKET => $hapaxToTypesBracket
		);
	}

	private static function _sanitizeRawBracket(array $rawOptions, string $key, 
		ABNet_PostStats_StyleMetricBracket $defaultBracket): array {
		
		if (empty($rawOptions[$key]) || !is_array($rawOptions[$key])) {
			return $defaultBracket->toArray();
		}

		$rawBracket = $rawOptions[$key];
		
		$min = isset($rawBracket['min'])
			? (float) $rawBracket['min']
			: $defaultBracket->getMin();
		$max = isset($rawBracket['max'])
			? (float) $rawBracket['max']
			: $defaultBracket->getMax();

		if (!is_finite($min)) {
			$min = $defaultBracket->getMin();
		}

		if (!is_finite($max)) {
			$max = $defaultBracket->getMax();
		}

		if ($min > $max) {
			$temp = $min;
			$min = $max;
			$max = $temp;
		}

		return array(
			'min' => $min,
			'max' => $max
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
		if ($this->_asArray === null) {
			$this->_asArray = array(
				self::KEY_USE_AVERAGE_SENTENCE_LENGTH => $this->_useAverageSentenceLength,
				self::KEY_USE_ENTROPY => $this->_useEntropy,
				self::KEY_USE_NEGATIVITY => $this->_useNegativity,
				self::KEY_USE_PUNCTUATION => $this->_usePunctuation,
				self::KEY_USE_LIX => $this->_useLix,
				self::KEY_USE_YULES_K => $this->_useYulesK,
				self::KEY_USE_HAPAX_TO_TYPES => $this->_useHapaxToTypes,
				self::KEY_NEGATIVE_WORD_LIST => $this->_negativeWordList,
				self::KEY_YULES_K_MULTIPLIER => $this->_yulesKMultiplier,
				self::KEY_AVERAGE_SENTENCE_LENGTH_BRACKET => $this->_averageSentenceLengthBracket->toArray(),
				self::KEY_ENTROPY_BRACKET => $this->_entryopyBracket->toArray(),
				self::KEY_NEGATIVITY_BRACKET => $this->_negativityBracket->toArray(),
				self::KEY_PUNCTUATION_BRACKET => $this->_punctuationBracket->toArray(),
				self::KEY_LIX_BRACKET => $this->_lixBracket->toArray(),
				self::KEY_YULES_K_BRACKET => $this->_yulesKBracket->toArray(),
				self::KEY_HAPAX_TO_TYPES_BRACKET => $this->_hapaxBracket->toArray()
			);
		}
		return $this->_asArray;
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

	public function hasNegativeWordList(): bool {
		return !empty($this->_negativeWordList);
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

	public function getAverageSentenceLengthBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_averageSentenceLengthBracket;
	}

	public function getEntropyBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_entryopyBracket;
	}

	public function getYulesKBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_yulesKBracket;
	}

	public function getNegativityBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_negativityBracket;
	}

	public function getPunctuationBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_punctuationBracket;
	}

	public function getLixBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_lixBracket;
	}

	public function getHapaxToTypesBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_hapaxBracket;
	}
}