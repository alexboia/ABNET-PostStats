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
 * @see https://www.paradigma.ro/p/negativitate
 */
class ABNet_PostStats_StyleMetricNegativityProvider implements ABNet_PostStats_StyleMetricProvider {
	public const WORD_BOUNDARY_REGEX = ABNet_PostStats_StyleSource::WORD_BOUNDARY_REGEX;
	
	private const DEFAULT_PRECISION = 1;

	private const SIMILARITY_THRESHOLD = 80;

	private array $_negativeWordList;

	public function __construct(array $negativeWordList = array()) {
		$this->_negativeWordList = $this->_prepareWordList($negativeWordList ?: self::getDefaultNegativeWordListRo());
	}

	private function _prepareWordList(array $negativeWordList): array {
		return array_map(function($word) {
			return $this->_prepare($word);
		}, $negativeWordList);
	}

	public static function getDefaultNegativeWordListRo(): array {
		return [
			'rau', 'urat', 'groaznic', 'teribil', 'ingrozitor', 'oribil', 'scarbos', 
			'dezgustator', 'respingator', 'repugnant', 'neplacut', 'deranjant',
			'suparator', 'enervant', 'iritant', 'frustrant', 'descurajant',
			'deprimant', 'trist', 'dureros', 'suferinta', 'chin', 'agonie',
			'tortura', 'cruzime', 'violenta', 'agresiune', 'ataca', 'distruge',
			'ucide', 'omoara', 'maltrateaza', 'abuzeaza', 'tortureaza',
			'raneste', 'dauneaza', 'deterioreaza', 'compromite', 'saboteaza',
			'esuiaza', 'pierde', 'rateaza', 'greseste', 'incurca', 'strica',
			'defect', 'rupt', 'deteriorat', 'corupt', 'toxic', 'periculos',
			'amenintator', 'infricosator', 'sperietoare', 'inspaimantator',
			'ingrijorator', 'alarmant', 'grav', 'sever', 'critic', 'fatal',
			'dezastru', 'catastrofa', 'tragedie', 'nenorocire', 'problema',
			'dificultate', 'obstacol', 'impediment', 'complicatie', 'criza',
			'conflict', 'razboi', 'lupta', 'bataie', 'cearta', 'disputa',
			'scandal', 'haos', 'dezordine', 'confuzie', 'nesiguranta',
			'teama', 'frica', 'panica', 'teroare', 'groaza', 'spaima',
			'ingrijorare', 'anxietate', 'stres', 'tensiune', 'presiune',
			'oboseala', 'epuizare', 'slabiciune', 'boala', 'suferind',
			'bolnav', 'ranit', 'accidentat', 'vatamat', 'handicapat',
			'invalid', 'dezabilitat', 'incapabil', 'neputincios', 'vulnerabil',
			'victima', 'prada', 'exploatat', 'manipulat', 'inselat',
			'tradat', 'abandonat', 'respins', 'exclus', 'izolat',
			'singur', 'parasit', 'nedorit', 'neacceptat', 'refuzat',
			'criticat', 'condamnat', 'acuzat', 'inculpat', 'vinovat',
			'pacatos', 'rusinos', 'jenant', 'umilitor', 'degradant',
			'n-am', 'nu', 'defel', 'nici', 'nimic', 'nimeni', 'nicicum', 'nicio',
			'niciodata', 'nicicand'
		];
	}

	public static function getDefaultNegativeWordListEn(): array {
		return [
			'bad', 'ugly', 'horrible', 'terrible', 'dreadful', 'awful', 'disgusting',
			'revolting', 'repulsive', 'repugnant', 'unpleasant', 'disturbing',
			'annoying', 'irritating', 'frustrating', 'discouraging',
			'depressing', 'sad', 'painful', 'suffering', 'pain', 'agony',
			'torture', 'cruelty', 'violence', 'aggression', 'attack', 'destroy',
			'kill', 'murder', 'abuse', 'torture',
			'hurt', 'harm', 'damage', 'compromise', 'sabotage',
			'fail', 'lose', 'miss', 'mistake', 'mess', 'break',
			'defective', 'broken', 'damaged', 'corrupt', 'toxic', 'dangerous',
			'threatening', 'frightening', 'scary', 'terrifying',
			'worrying', 'alarming', 'serious', 'severe', 'critical', 'fatal',
			'disaster', 'catastrophe', 'tragedy', 'misfortune', 'problem',
			'difficulty', 'obstacle', 'impediment', 'complication', 'crisis',
			'conflict', 'war', 'fight', 'battle', 'argument', 'dispute',
			'scandal', 'chaos', 'disorder', 'confusion', 'uncertainty',
			'fear', 'afraid', 'panic', 'terror', 'horror', 'dread',
			'worry', 'anxiety', 'stress', 'tension', 'pressure',
			'fatigue', 'exhaustion', 'weakness', 'illness', 'suffering',
			'sick', 'injured', 'hurt', 'wounded', 'handicapped',
			'disabled', 'unable', 'powerless', 'vulnerable',
			'victim', 'prey', 'exploited', 'manipulated', 'deceived',
			'betrayed', 'abandoned', 'rejected', 'excluded', 'isolated',
			'alone', 'lonely', 'unwanted', 'unaccepted', 'refused',
			'criticized', 'condemned', 'accused', 'blamed', 'guilty',
			'shameful', 'embarrassing', 'humiliating', 'degrading',
			'no', 'not', 'never', 'nothing', 'nobody', 'none', 'neither'
		];
	}

	public function compute(ABNet_PostStats_StyleSource $source): ABNet_PostStats_StyleMetric {	
		$plainText = $source->getPlainText();
		$sentences = preg_split('/[.!?\n]+/', $plainText, -1, PREG_SPLIT_NO_EMPTY);
		
		$sentenceCount = count($sentences);
		$negativeSenteceCount  = $this->_computeNegativeSentenceCount($sentences);

		$negativity = round(($negativeSenteceCount / $sentenceCount) * 100, 0);
		$friendly = $this->_friendlyRepresentation($negativity);

		return new ABNet_PostStats_StyleMetric(
			$this->getKey(),
			$this->getName(),
			$this->getShortDescription(), 
			$negativity,
			'%',
			$friendly
		);
	}

	private function _computeNegativeSentenceCount(array $sentences): int {
		$negativeSenteceCount = 0;
		//The values in _negativeWordList are already prepared
		$negativeWordMap = array_flip($this->_negativeWordList);

		foreach ($sentences as $sentence) {
			$sentence = $this->_prepare($sentence);

			preg_match_all(self::WORD_BOUNDARY_REGEX, $sentence, $sentenceWords);
			if (empty($sentenceWords[0]) || !is_array($sentenceWords[0])) {
				continue;
			}

			foreach ($sentenceWords[0] as $word) {
				$word = trim($word);
				//Fast but quite restrictive
				if (isset($negativeWordMap[$word])) {
					$negativeSenteceCount += 1;
					break;
				}

				//Attempt to find by similarity as well
				$findBySimilarity = array_find($this->_negativeWordList, 
					function($matchWord) use($word) {
						$percentage = 0;
						similar_text($word, $matchWord, $percentage);
						return $percentage >= self::SIMILARITY_THRESHOLD;
					});

				if ($findBySimilarity !== null) {
					$negativeSenteceCount += 1;
					break;
				}
			}
		}

		return $negativeSenteceCount;
	}

	private function _friendlyRepresentation(float $negativity): string {
		return sprintf('%.' . self::DEFAULT_PRECISION . 'f/10 (N%%)', $negativity / 10);
	}

	private function _prepare(string $str): string {
		$str = remove_accents(trim($str));
		return function_exists('mb_strtoupper') 
			? mb_strtoupper($str)
			: strtoupper($str);
	}

	public function getKey(): string {
		return 'negativity';
	}

	public function getName(): string {
		return __("Negativity", 'abnet-post-stats');
	}

	public function getShortDescription(): string {
		return __(
			"Negativity is a simple score that estimates the negative tone of a text by measuring the percentage of negative sentences.", 
			'abnet-post-stats'
		);
	}
}
