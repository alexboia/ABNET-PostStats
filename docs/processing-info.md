# Text processing notes

Somewhat nerd alert.

## Word boundaries

Regex Pattern: `/\b\w+\b/u`

What it does:

- Finds words by matching runs of word characters between word boundaries.
- Uses Unicode mode, so it can process non-ASCII text more safely than non-Unicode regex mode.
- Returns all matched words, which are later normalized to uppercase and used to build word-frequency metrics.

Why it matters in this plugin:

- It is the main tokenization step for lexical metrics.
- Raw word count comes from the number of matches.
- Unique word count and the word count map depend directly on these matches.

Notes:

- Because it relies on `\w`, behavior for diacritics depends on PCRE Unicode character handling.
- Numbers and underscore-based tokens can also be matched as words.

## Recognized punctuation

Pattern: `/[.,;:?|\-…\'"()\[\]{}\/\\@#*_]/u`

What it does:

- Matches one punctuation symbol at a time from a predefined set.
- The set includes common punctuation marks and symbols such as dot, comma, question mark, exclamation-adjacent punctuation markers, brackets, slash, backslash, at sign, hash, star, underscore, and ellipsis.
- Runs globally via preg_match_all to collect every punctuation occurrence.

Why it matters in this plugin:

- Feeds punctuation frequency distribution used by punctuation-based style metrics.
- Raw punctuation count is the total number of symbol matches.
- Unique punctuation count is derived from the punctuation frequency map.

Notes:

- Only symbols listed in the character class are counted.
- If a text uses punctuation not present in this class, it will be ignored by punctuation metrics.

## Sentence boundaries

Pattern: `/[.!?\n]+/u`

What it does:

- Treats sentence boundaries as any sequence of one or more of: period, exclamation mark, question mark, or newline.
- Collapses repeated boundary characters into one split boundary because of the plus quantifier.

Why it matters in this plugin:

- It defines how sentence segmentation is performed for sentence-level metrics, especially average sentence length.
- Directly influences sentence count and therefore any metric that divides by number of sentences.

Notes:

- Including newline as a sentence boundary makes line-broken text split into more sentences.
- Abbreviations containing periods may increase perceived sentence count, depending on how sentence extraction is implemented in helper code.
