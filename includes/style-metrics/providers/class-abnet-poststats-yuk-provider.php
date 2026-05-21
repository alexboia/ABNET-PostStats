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
 * @see https://www.paradigma.ro/p/yuk
 * @see https://github.com/alexboia/ABNET-PostStats/blob/main/docs/yuk.md
 */
class ABNet_PostStats_StyleMetricYulesKProvider implements ABNet_PostStats_StyleMetricProvider {
	public const KEY = 'yuk';

	private const DEFAULT_YULESK_MULTIPLIER = 10000;

	private const DEFAULT_PRECISION = 0;
	
	private int $_yulesKMultiplier = self::DEFAULT_YULESK_MULTIPLIER;

	private ABNet_PostStats_StyleMetricBracket $_bracket;

	public function __construct(int $yulsesKMultiplier = 0, ?ABNet_PostStats_StyleMetricBracket $bracket = null) {
		$this->_yulesKMultiplier = $yulsesKMultiplier;
		if ($this->_yulesKMultiplier <= 0) {
			$this->_yulesKMultiplier = self::DEFAULT_YULESK_MULTIPLIER;
		}

		$this->_bracket = $bracket ?? ABNet_PostStats_StyleMetricBracket::unbounded();
	}

    public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric { 
		$distribution = $this->_getWordCountDistibution($source);
		$secondMoment = $this->_computeYulesKSecondMoment($distribution);
		$totalWordCount = $source->getRawWordCount();

		if ($totalWordCount > 0) {
			$yulesK = $this->_yulesKMultiplier * (
				($secondMoment - $totalWordCount) / 
				($totalWordCount * $totalWordCount)
			);
			
			$yulesK = round($yulesK, self::DEFAULT_PRECISION);
		} else {
			$yulesK = 0;
		}

		$friendly = $this->_getFriendRepresentation($yulesK);;

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(), 
			$this->getName(), 
			$this->getShortDescription(), 
			$yulesK, 
			null, 
			$friendly,
			$this->_bracket
		);
	}

	private function _getFriendRepresentation($yuleK) {
		return sprintf('%.' . self::DEFAULT_PRECISION ."f (YK)", $yuleK);
	}

	private function  _getWordCountDistibution(ABNet_PostStats_StyleSource $source) :array {
		$distribution = array();

		foreach ($source->getWordCountMap() as $count) {
			if (!isset($distribution[$count])) {
				$distribution[$count] = 0;
			}

			$distribution[$count]++;
		}

		return $distribution;
	}

	private function _computeYulesKSecondMoment(array $distribution) {
		$secondMoment = 0;
		foreach ($distribution as $wordCount => $wordCountDistribution) {
			$secondMoment += (($wordCount * $wordCount) * $wordCountDistribution);
		}

		return $secondMoment;
	}

    public function getKey(): string { 
		return self::KEY;
	}

    public function getName(): string { 
		return __("Yule's K", 'abnet-post-stats');
	}

    public function getShortDescription(): string { 
		return __("Yule's K is a stylometric index that measures the richness of vocabulary in a text.", 'abnet-post-stats');
	}

	public function getBracket(): ABNet_PostStats_StyleMetricBracket {
		return $this->_bracket;
	}
}