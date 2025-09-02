# Punctuation

The "Punctuation" indicator measures the frequency of punctuation marks in a text and reflects the writing style, rhythm, and complexity of sentences used by the author.

A fragmented, expressive, or rhetorical style will have a different score compared to a sober, concise, or technical one.

## How is it calculated?

Punctuation is expressed as a percentage of the total words in the text:

**Punctuation Score = (Number of punctuation marks / Total number of words) × 100**

This percentage shows how much punctuation appears, on average, per word, and is numerically identical to "per 100 words" normalization. Therefore, a value like "10" can be interpreted either as "10 punctuation marks per 100 words" or as "10% punctuation". Choose the variant that seems more natural or easier to understand.

**Note:** Although expressed as a percentage, this indicator does not reflect the percentage of total characters, but the frequency relative to words.

## Why don't we normalize punctuation by characters or sentences?

- **Characters** (letters, spaces, digits) are not a significant unit of expression in stylometry. For example, in German, words tend to be longer than in Romanian, but that doesn't mean it has less punctuation, so normalization by characters would be misleading.

- **Sentences** can vary enormously in length. A poetic text might have one sentence across 3 lines, while a legal text might have 10 sentences in a single paragraph.

Normalization by words is the most stable and relevant method.

## What punctuation marks are included?

List of characters typically analyzed:

- `.` — period (end of sentence)
- `,` — comma (fragmented style, long phrases)
- `;` — semicolon (formal, academic style)
- `:` — colon (internal structuring)
- `?` — question mark (interrogative, rhetorical)
- `!` — exclamation mark (emotion, expressiveness)
- `—` or `–` — em dash or en dash (colloquial, parenthetical style)
- `...` — ellipsis (hesitation, subjective narrative style)
- `"` and `'` — quotation marks (dialogue, quotes)
- `()` — parentheses (explanations)

In some cases, these can also be analyzed:

- `/` — technical language, data
- `@`, `#` — social media text
- `*`, `_` — emphasis in markdown style

## What can this indicator reveal?

- Authors who overuse punctuation (`!!!`, `...`, `?!`) may have an informal, expressive, or disorganized style.
- Soberly written texts (reports, press releases) will have discreet and balanced punctuation.
- Punctuation can contribute to author identification, text classification, and detection of simulated styles (including AI vs. human). For example, AI-generated texts abound in "—", while human authors use them much more rarely.

---

*Source: Translated from [Paradigma.ro - Punctuație](https://www.paradigma.ro/p/punctuatie)*
