<?php
/**
 * @package ABNet_Post_Stats
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * @see https://www.paradigma.ro/p/asl
 */
class ABNet_PostStats_StyleMetricAverageSentenceLengthProvider implements ABNet_PostStats_StyleMetricProvider {
	private const DEFAULT_PRECISION = 0;

	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {
		$wordCount = $source->getRawWordCount();
		$sentenceCount = $this->_countSentences($source);

		$asl = round($wordCount / $sentenceCount, self::DEFAULT_PRECISION);
		$friendly = sprintf('%f (ASL)', $asl);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$asl, 
			null,
			$friendly
		);
	}

	private function _countSentences(ABNet_PostStats_StyleSource $source): int {
		$text = $source->getPlainText();
		$text = trim($text);

		if (empty($text)) {
			return 0;
		}

		// Remove multiple spaces and normalize whitespace
		$text = preg_replace('/\s+/', ' ', $text);

		// Count sentences by looking for sentence-ending punctuation
		// followed by whitespace or end of string, or newlines as sentence terminators
		$sentenceCount = preg_match_all('/[.!?\n]+(?:\s|$)/u', $text);
		return max(1, $sentenceCount);
	}

	public function getKey(): string {
		return 'average-sentence-length';
	}

	public function getName(): string {
		return __("Average Sentence Length", 'abnet-post-stats');
	}

	public function getShortDescription(): string {
		return __(
			"ASL (Average Sentence Length) is a classic indicator of readability and clarity of expression.", 
			'abnet-post-stats'
		);
	}
}