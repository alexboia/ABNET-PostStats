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
 * @see https://www.paradigma.ro/p/yuk
 */
class ABNet_PostStats_StyleMetricYuleKProvider implements ABNet_PostStats_StyleMetricProvider {
	private const DEFAULT_YULES_K_C = 10000;

	private const DEFAULT_PRECISION = 0;
	
	private int $_yulesKC = self::DEFAULT_YULES_K_C;

	public function __construct(int $yulsesKC) {
		$this->_yulesKC = $yulsesKC;
		if ($this->_yulesKC <= 0) {
			$this->_yulesKC = self::DEFAULT_YULES_K_C;
		}
	}

    public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric { 
		$distribution = $this->_getWordCountDistibution($source);
		$secondMoment = $this->_computeYulesKSecondMoment($distribution);
		$totalWordCount = $source->getRawWordCount();

		$yulesK = $this->_yulesKC * (
			($secondMoment - $totalWordCount) / 
			($totalWordCount * $totalWordCount)
		);
		
		$yulesK = round($yulesK, self::DEFAULT_PRECISION);

		$friendly = $this->_getFriendRepresentation($yulesK);;

		return new ABNet_PostStats_StyleMetric($this->getKey(), 
			$this->getName(), 
			$this->getShortDescription(), 
			$yulesK, 
			null, 
			$friendly);
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
		return 'yuk';
	}

    public function getName(): string { 
		return __("Yule's K", 'abnet-post-stats');
	}

    public function getShortDescription(): string { 
		return __("Yule's K is a stylometric index that measures the richness of vocabulary in a text.", 'abnet-post-stats');
	}
}