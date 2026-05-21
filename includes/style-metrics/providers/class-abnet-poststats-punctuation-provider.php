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
 * @see https://www.paradigma.ro/p/punctuatie
 * @see https://github.com/alexboia/ABNET-PostStats/blob/main/docs/punctuation.md
 */
class ABNet_PostStats_StyleMetricPunctuationProvider implements ABNet_PostStats_StyleMetricProvider {
	public const KEY = 'punctuation';

	private const DEFAULT_PRECISION = 1;
	
	public const PUNCTUATION_REGEX = ABNet_PostStats_StyleSource::PUNCTUATION_REGEX;

	private ABNet_PostStats_StyleMetricBracket $_bracket;

	public function __construct(?ABNet_PostStats_StyleMetricBracket $bracket = null){
		$this->_bracket = $bracket ?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}

	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {	
		$punctuationCount = $source->getRawPunctuationCount();
		$totalWordCount = $source->getRawWordCount();
		
		$punctuation = $totalWordCount > 0 
			? round(($punctuationCount / $totalWordCount) * 100, 0)
			: 0;
			
		$friendly = $this->_friendlyRepresentation($punctuation);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$punctuation,
			'%',
			$friendly,
			$this->_bracket
		);
	}

	private function _friendlyRepresentation(float $punctuation): string {
		return sprintf('%d%% (P%%)', (int)$punctuation);
	}

	public function getKey(): string {
		return self::KEY;
	}

	public function getName(): string {
		return __("Punctuation", 'abnet-post-stats');
	}

	public function getShortDescription(): string {
		return __(
			"The Punctuation indicator measures the frequency of punctuation marks in a text and reflects the writing style, rhythm, and complexity of the sentences used by the author.", 
			'abnet-post-stats'
		);
	}

	public function getBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_bracket;
	}
}