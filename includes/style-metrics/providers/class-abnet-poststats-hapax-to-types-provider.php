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
 * @see https://www.paradigma.ro/p/hapax
 * @see https://github.com/alexboia/ABNET-PostStats/blob/main/docs/hapax-to-types.md
 */
class ABNet_PostStats_StyleMetricHapaxToTypesProvider implements ABNet_PostStats_StyleMetricProvider {
	public const KEY = 'hapax-to-types';

	private const DEFAULT_PRECISION = 1;

	private ABNet_PostStats_StyleMetricBracket $_bracket;

	public function __construct(?ABNet_PostStats_StyleMetricBracket $bracket = null) {
		$this->_bracket = $bracket ?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}
	
	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric { 
		$wordsThatAppearOnlyOnce = $this->_countWordsThatAppearOnlyOnce($source);
		$totalTypes = $source->getUniqueWordCount();

		$hapax = $totalTypes > 0 
			? round($wordsThatAppearOnlyOnce / $totalTypes, self::DEFAULT_PRECISION)
			: 0;
			
		$friendly = $this->_friendlyRepresentation($hapax);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$hapax,
			'%',
			$friendly,
			$this->_bracket
		);
	}

	private function _countWordsThatAppearOnlyOnce(ABNet_PostStats_StyleSource $source): int {
		$uniqeWordCount = 0;
		foreach ($source->getWordCountMap() as $perWordCount) {
			if ($perWordCount == 1) {
				$uniqeWordCount ++;
			}
		}

		return $uniqeWordCount;
	}

	private function _friendlyRepresentation(float $hapax): string {
		return sprintf('%d%% (HTR %%)', $hapax * 100);
	}

    public function getKey(): string { 
		return self::KEY;
	}

    public function getName(): string { 
		return __('Hapax to Types Ratio', 'abnet-post-stats');
	}

	public function getShortDescription(): string {
		return __('In linguistics and stylometry, hapax legomena are words that appear only once in a text.', 'abnet-post-stats');
	}

	public function getBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_bracket;
	}
}