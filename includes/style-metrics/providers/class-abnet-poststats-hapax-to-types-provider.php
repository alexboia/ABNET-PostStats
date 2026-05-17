<?php
/**
 * @package ABNet_PostStats
 * @since 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

/**
 * @see https://www.paradigma.ro/p/hapax
 */
class ABNet_PostStats_StyleMetricHapaxToTypesProvider implements ABNet_PostStats_StyleMetricProvider {
    private const DEFAULT_PRECISION = 1;
	
	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric { 
		$wordsThatAppearOnlyOnce = $this->_countWordsThatAppearOnlyOnce($source);
		$totalTypes = $source->getUniqueWordCount();

		$hapax = round($wordsThatAppearOnlyOnce / $totalTypes, self::DEFAULT_PRECISION);
		$friendly = $this->_friendlyRepresentation($hapax);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(),
			$hapax,
			'%',
			$friendly
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
		return 'hapax-to-types';
	}

    public function getName(): string { 
		return __('Hapax to Types Ratio', 'abnet-post-stats');
	}

    public function getShortDescription(): string { 
		return __('In linguistics and stylometry, hapax legomena are words that appear only once in a text.', 'abnet-post-stats');
	}
}