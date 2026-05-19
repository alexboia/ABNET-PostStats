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
 * @see https://www.paradigma.ro/p/entropie 
 * @see https://github.com/alexboia/ABNET-PostStats/blob/main/docs/shannon-entropy.md
 */
class ABNet_PostStats_StyleMetricEntropyProvider implements ABNet_PostStats_StyleMetricProvider {
	public const KEY = 'shannon-entropy';

	private const DEFAULT_PRECISION = 1;

	private ABNet_PostStats_StyleMetricBracket $_bracket;

	public function __construct(?ABNet_PostStats_StyleMetricBracket $bracket = null){
		$this->_bracket = $bracket ?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}
	
	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {
		$entropy = $this->_computeEntropy($source);
		$friendly = $this->_friendlyRepresentation($entropy);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(), 
			$this->getName(), 
			$this->getShortDescription(), 
			$entropy, 
			'%', 
			$friendly,
			$this->_bracket
		);
	}

	private function _computeEntropy(ABNet_PostStats_StyleSource $source): float {
		$sum = 0;

		if ($source->getRawWordCount() > 0) {
			foreach ($source->getWordCountMap() as $word => $count) {
				if ($count > 0) {
					$proportion = $count / $source->getRawWordCount();
					$sum += $proportion * log($proportion, 2);
				}
			}
		}

		$entropy = round((-1) * $sum, self::DEFAULT_PRECISION);
		return $entropy;
	}

	private function _friendlyRepresentation(float $entropy): string {
		return sprintf('%.' . self::DEFAULT_PRECISION . 'f/10 (E)', $entropy);
	}

	public function getKey(): string {
		return self::KEY;
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

	public function getBracket(): ABNet_PostStats_StyleMetricBracket{
		return $this->_bracket;
	}
}