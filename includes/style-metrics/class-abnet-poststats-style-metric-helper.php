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

class ABNet_PostStats_StyleMetricHelper {
	public static function countSentences(ABNet_PostStats_StyleSource $source): int {
		$text = $source->getPlainText();
		$text = trim($text);

		if (empty($text)) {
			return 0;
		}

		// Remove multiple spaces and normalize whitespace
		$text = preg_replace('/\s+/', ' ', $text);

		// Count sentences by looking for sentence-ending punctuation
		// followed by whitespace or end of string, or newlines as sentence terminators
		$sentenceCount = preg_match_all(ABNet_PostStats_StyleSource::SENTENCE_BOUNDARY_REGEX, $text);
		return max(1, $sentenceCount);
	}
}