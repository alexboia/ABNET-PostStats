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
 * @see https://www.paradigma.ro/p/entropie 
 */
class ABNet_PostStats_StyleMetricEntropyProvider implements ABNet_PostStats_StyleMetricProvider {
	private const DEFAULT_PRECISION = 1;
	
	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {
		$sum = 0;

		foreach ($source->getWordCountMap() as $word => $count) {
			if ($count > 0) {
				$proportion = $count / $source->getRawWordCount();
				$sum += $proportion * log($proportion, 2);
			}
		}

		$entropy = round((-1) * $sum, self::DEFAULT_PRECISION);
		$friendly = $this->_friendlyRepresentation($entropy);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(), 
			$this->getName(), 
			$this->getShortDescription(), 
			$entropy, 
			'%', 
			$friendly
		);
	}

	private function _friendlyRepresentation(float $entropy): string {
		return sprintf('%d/10 (E)', $entropy * 10);
	}

	public function getKey(): string {
		return 'shannon-entropy';
	}

	public function getName(): string {
		return __("Shannon's Entropy", 'abnet-post-stats');
	}

	public function getShortDescription(): string {
		return __(
			"In stylometry, entropy measures the diversity and unpredictability of the language used in a text. It indicates how much new information each unit (word or character) brings and how predictable their distribution is.", 
			'abnet-post-stats'
		);
	}
}