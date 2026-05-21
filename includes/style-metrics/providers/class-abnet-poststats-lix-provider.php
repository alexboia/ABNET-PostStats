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
 * @see https://www.paradigma.ro/p/lix
 * @see https://github.com/alexboia/ABNET-PostStats/blob/main/docs/lix.md
 */
class ABNet_PostStats_StyleMetricLixProvider implements ABNet_PostStats_StyleMetricProvider {
	public const KEY = 'lix';

	private const DEFAULT_PRECISION = 0;
	
	private int $_longWordThreshold = 6;

	private ABNet_PostStats_StyleMetricBracket $_bracket;

	public function __construct(?ABNet_PostStats_StyleMetricBracket $bracket = null) {
		$this->_bracket = $bracket ?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}
	
	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric { 
		$totalWordCount = $source->getRawWordCount();
		$totalSentenceCount = $this->_countSentences($source);
		$longWordCount = $this->_countLongWords($source);

		$lix = $totalSentenceCount > 0 && $totalWordCount > 0 
			? ($totalWordCount /  $totalSentenceCount) + (($longWordCount / $totalWordCount) * 100)
			: 0;

		$lix = round($lix, self::DEFAULT_PRECISION);
		$friendly = sprintf('%d (LIX)', (int)$lix);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$lix, 
			null,
			$friendly,
			$this->_bracket
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
		return self::KEY;
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

	public function getBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_bracket;
	}
}