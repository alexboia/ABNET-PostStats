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
 * @see https://www.paradigma.ro/p/asl
 * @see https://github.com/alexboia/ABNET-PostStats/blob/main/docs/average-sentence-length.md
 */
class ABNet_PostStats_StyleMetricAverageSentenceLengthProvider implements ABNet_PostStats_StyleMetricProvider {
	public const KEY = 'average-sentence-length';

	private const DEFAULT_PRECISION = 0;

	private ABNet_PostStats_StyleMetricBracket $_bracket;

	public function __construct(?ABNet_PostStats_StyleMetricBracket $bracket = null) {
		$this->_bracket = $bracket ?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}

	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {
		$wordCount = $source->getRawWordCount();
		$sentenceCount = $this->_countSentences($source);

		$asl = round($wordCount / $sentenceCount, self::DEFAULT_PRECISION);
		$friendly = sprintf('%.' . self::DEFAULT_PRECISION . 'f (ASL)', $asl);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$asl, 
			null,
			$friendly,
			$this->_bracket
		);
	}

	private function _countSentences(ABNet_PostStats_StyleSource $source): int {
		return ABNet_PostStats_StyleMetricHelper::countSentences($source);
	}

	public function getKey(): string {
		return self::KEY;
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

	public function getBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_bracket;
	}
}