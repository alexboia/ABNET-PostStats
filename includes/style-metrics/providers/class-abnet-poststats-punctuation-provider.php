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
 * @see https://www.paradigma.ro/p/punctuatie
 */
class ABNet_PostStats_StyleMetricPunctuationProvider implements ABNet_PostStats_StyleMetricProvider {
	private const DEFAULT_PRECISION = 1;
	
	public const PUNCTUATION_REGEX = ABNet_PostStats_StyleSource::PUNCTUATION_REGEX;

	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {	
		$punctuationCount = $source->getRawPunctuationCount();
		$totalWordCount = $source->getRawWordCount();
		
		$punctuation = round($punctuationCount / $totalWordCount * 100, 0);
		$friendly = $this->_friendlyRepresentation($punctuation);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$punctuation,
			'%',
			$friendly
		);
	}

	private function _friendlyRepresentation(float $punctuation): string {
		return sprintf('%.' . self::DEFAULT_PRECISION . 'f/10 (P%%)', $punctuation / 10);
	}

	public function getKey(): string {
		return 'punctuation';
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
}