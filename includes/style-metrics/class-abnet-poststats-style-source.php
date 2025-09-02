<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

class ABNet_PostStats_StyleSource {
	public const WORD_BOUNDARY_REGEX = '/\b\w+\b/u';

	public const PUNCTUATION_REGEX = '/[.,;:?|\-…\'"()\[\]{}\/\\@#*_]/u';

	private string $_rawText;

	private string $_plainText;

	private array $_allWords;

	private array $_wordCountMap;

	private array $_punctuationCountMap;

	private int $_rawWordCount;

	private int $_rawPunctuationCount;

	public function __construct(string $rawText, array $completelyRemoveTags = array('pre', 'code', 'table')) {
		$this->_rawText = $rawText;
		$this->_plainText = $this->_cleanRawText($rawText, $completelyRemoveTags);
		$this->_computeMarkers();
	}

	private function _cleanRawText(string $rawText, array $completelyRemoveTags): string {
		foreach ($completelyRemoveTags as $tag) {
			$pattern = '/<' . preg_quote($tag, '/') . '\b[^>]*>.*?<\/' . preg_quote($tag, '/') . '>/is';
			$rawText = preg_replace($pattern, '', $rawText);
		}
		
		return trim(strip_shortcodes(wp_strip_all_tags($rawText)));
	}

	private function _computeMarkers() {
		/**
		 * Extracts all words from the plain text content using word boundary matching.
		 * 
		 * Uses a Unicode-compatible regular expression pattern that:
		 * - \b matches word boundaries (transitions between word and non-word characters)
		 * - \w+ matches one or more word characters (letters, digits, underscores)
		 * - /u flag enables Unicode mode for proper handling of international characters
		 * 
		 * The matches are stored in the $matches array where $matches[0] contains
		 * all found words as separate array elements.
		 */
		preg_match_all(self::WORD_BOUNDARY_REGEX, $this->_plainText, $matches);
		if (!empty($matches[0])) {
			$this->_allWords = function_exists('mb_strtoupper')
				? array_map('mb_strtoupper', $matches[0])
				: array_map('strtoupper', $matches[0]);
		} else {
			$this->_allWords = array();
		}
				
		$this->_wordCountMap = array_count_values($this->_allWords);
		$this->_rawWordCount = count($this->_allWords);
		
		// Extract punctuation and count frequencies
		preg_match_all(self::PUNCTUATION_REGEX, $this->_plainText, $punctMatches);
		if (!empty($punctMatches[0])) {
			$this->_punctuationCountMap = array_count_values($punctMatches[0]);
			$this->_rawPunctuationCount = count($punctMatches[0]);
		} else {
			$this->_punctuationCountMap = array();
		}			
	}

	public function getRawText(): string {
		return $this->_rawText;
	}

	public function getPlainText(): string {
		return $this->_plainText;
	}

	public function getAllWords(): array {
		return $this->_allWords;
	}

	public function getWordCountMap(): array {
		return $this->_wordCountMap;
	}

	public function getPunctuationCountMap(): array {
		return $this->_punctuationCountMap;
	}

	public function getRawWordCount(): int {
		return $this->_rawWordCount;
	}

	public function getUniqueWordCount(): int {
		return count($this->_wordCountMap);
	}

	public function getRawPunctuationCount(): int {
		return $this->_rawPunctuationCount;
	}

	public function getUniquePunctuationCount(): int {
		return count($this->_punctuationCountMap);
	}
}