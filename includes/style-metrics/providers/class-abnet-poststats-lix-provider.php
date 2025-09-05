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
 * @see https://www.paradigma.ro/p/lix
 */
class ABNet_PostStats_StyleMetricLixProvider implements ABNet_PostStats_StyleMetricProvider {
    private const DEFAULT_PRECISION = 0;
	
	private int $_longWordThreshold = 6;
	
	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric { 
		$totalWordCount = $source->getRawWordCount();
		$totalSentenceCount = $this->_countSentences($source);
		$longWordCount = $this->_countLongWords($source);

		$lix = ($totalWordCount /  $totalSentenceCount) 
			+ (($longWordCount / $totalWordCount) * 100);

		$lix = round($lix, self::DEFAULT_PRECISION);
		$friendly = sprintf('%d (LIX)', (int)$lix);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$lix, 
			null,
			$friendly
		);
	}

	private function _countSentences(ABNet_PostStats_StyleSource $source): int {
		return ABNet_PostStats_StyleMetricHelper::countSentences($source);
	}

	private function _countLongWords(ABNet_PostStats_StyleSource $source): int {
		$count = 0;
		$words = $source->getAllWords();
		$lengthCalculator = function_exists('mb_strlen')
			? 'mb_strlen'
			: 'strlen';
		
		array_walk($words, function($word, $index) use($count, $lengthCalculator) {
			$length = $lengthCalculator($word);
			if ($length >= $this->_longWordThreshold) {
				$count += 1;
			}
		});

		return $count;
	}

    public function getKey(): string { 
		return 'lix';
	}

    public function getName(): string { 
		return __('LIX Score', 'abnet-post-stats');
	}

    public function getShortDescription(): string { 
		return __(
			'The LIX score is a simple formula for measuring the degree of difficulty of a written text.', 
			'abnet-post-stats'
		);
	}

}