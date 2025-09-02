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
		return ABNet_PostStats_StyleMetricHelper::countSentences($source);
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