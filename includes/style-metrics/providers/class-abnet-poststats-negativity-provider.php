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

/**
 * @see https://www.paradigma.ro/p/negativitate
 * @see https://github.com/alexboia/ABNET-PostStats/blob/main/docs/negativity.md
 */
class ABNet_PostStats_StyleMetricNegativityProvider implements ABNet_PostStats_StyleMetricProvider {
	public const KEY = 'negativity';

	public const WORD_BOUNDARY_REGEX = ABNet_PostStats_StyleSource::WORD_BOUNDARY_REGEX;
	
	private const DEFAULT_PRECISION = 1;

	private const SIMILARITY_THRESHOLD = 80;

	private array $_negativeWordList;

	private ABNet_PostStats_StyleMetricBracket $_bracket;
	
	public function __construct(array $negativeWordList = array(), ?ABNet_PostStats_StyleMetricBracket $bracket = null) {
		$negativeWordList = !empty($negativeWordList) ? $negativeWordList : self::getDefaultNegativeWordList();
		$this->_negativeWordList = $this->_prepareWordList($negativeWordList);

		$this->_bracket = $bracket ?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}

	private function _prepareWordList(array $negativeWordList): array {
		return array_map(function($word) {
			return $this->_prepare($word);
		}, $negativeWordList);
	}

	public static function getDefaultNegativeWordList(): array {
		$defaultList = self::getDefaultNegativeWordListRo();
		return apply_filters('abnet_posts_stats_default_negative_word_list', $defaultList);
	}

	public static function getDefaultNegativeWordListRo(): array {
		return self::_readWordList('default-negative-word-list-ro.json');
	}

	public static function getDefaultNegativeWordListEn(): array {
		return self::_readWordList('default-negative-word-list-en.json');
	}

	/**
	 * @return string[]
	 */
	private static function _readWordList(string $fileName): array {
		$jsonFile = ABNET_POST_STATS_DATA_DIR . $fileName;
		if (is_readable($jsonFile)) {
			$jsonContent = file_get_contents($jsonFile);
			if (empty($jsonContent)) {
				return [];
			}

			$wordList = json_decode($jsonContent, false);
			return is_array($wordList) ? $wordList : [];
		} else {
			error_log(sprintf('[ERROR] Default negative word list file <%s> not readable or not found.', $jsonFile));
			return [];
		}
	}

	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {	
		$plainText = $source->getPlainText();
		$sentences = preg_split(ABNet_PostStats_StyleSource::SENTENCE_BOUNDARY_REGEX, $plainText, -1, PREG_SPLIT_NO_EMPTY);
		
		$sentenceCount = count($sentences);
		$negativeSenteceCount  = $this->_computeNegativeSentenceCount($sentences);

		$negativity = round(($negativeSenteceCount / $sentenceCount) * 100, 0);
		$friendly = $this->_friendlyRepresentation($negativity);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(), 
			$negativity,
			'%',
			$friendly,
			$this->_bracket
		);
	}

	private function _computeNegativeSentenceCount(array $sentences): int {
		$negativeSenteceCount = 0;
		//The values in _negativeWordList are already prepared
		$negativeWordMap = array_flip($this->_negativeWordList);

		foreach ($sentences as $sentence) {
			$sentence = $this->_prepare($sentence);

			preg_match_all(self::WORD_BOUNDARY_REGEX, $sentence, $sentenceWords);
			if (empty($sentenceWords[0]) || !is_array($sentenceWords[0])) {
				continue;
			}

			foreach ($sentenceWords[0] as $word) {
				$word = trim($word);
				//Fast but quite restrictive
				if (isset($negativeWordMap[$word])) {
					$negativeSenteceCount += 1;
					break;
				}

				//Attempt to find by similarity as well
				$findBySimilarity = array_find($this->_negativeWordList, 
					function($matchWord) use($word) {
						$percentage = 0;
						similar_text($word, $matchWord, $percentage);
						return $percentage >= self::SIMILARITY_THRESHOLD;
					});

				if ($findBySimilarity !== null) {
					$negativeSenteceCount += 1;
					break;
				}
			}
		}

		return $negativeSenteceCount;
	}

	private function _friendlyRepresentation(float $negativity): string {
		return sprintf('%d%% (N%%)', (int)$negativity);
	}

	private function _prepare(string $str): string {
		$str = remove_accents(trim($str));
		return function_exists('mb_strtoupper') 
			? mb_strtoupper($str)
			: strtoupper($str);
	}

	public function getKey(): string {
		return self::KEY;
	}

	public function getName(): string {
		return __("Negativity", 'abnet-post-stats');
	}

	public function getShortDescription(): string {
		return __(
			"Negativity is a simple score that estimates the negative tone of a text by measuring the percentage of negative sentences.", 
			'abnet-post-stats'
		);
	}

	public function getBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_bracket;
	}
}
