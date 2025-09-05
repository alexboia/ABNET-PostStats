# Yule's K

**Yule's K is a stylometric index that measures the richness of vocabulary in a text.**

## What is Yule's K?

Yule's K is a stylometric index that measures the richness of vocabulary in a text. It was proposed by George Udny Yule, a British statistician, in the 1940s, as part of his research on lexical diversity and the structure of written language.

The K score is interesting because it doesn't rely solely on the proportion of unique words, but takes into account the distribution of word frequency, offering a more precise estimate of lexical repetition. It has become popular in fields such as literary style analysis, computational linguistics, and author detection.

## How is Yule's K calculated?

The mathematical formula is:

**K = 10,000 × (M2 - N) / N²**

Where:

- **N** = total number of words in the text (tokens)
- **M2** = weighted sum of the squares of word frequencies

The calculation may seem complex, but essentially it's based on how often words are repeated:

- If a word appears **1 time** → it is considered rare
- If it appears **10 times** → it contributes massively to the K value

A text where many words are repeated frequently will have a higher K, indicating that the vocabulary is poor or limited.

## How do we interpret the Yule's K score?

Yule's K typically varies between 30 and 150, depending on the length and complexity of the text. Unlike LIX, here a lower score is better, signaling a richer and more varied vocabulary.

| Yule's K | Interpretation | Text Type | Example |
|----------|----------------|-----------|---------|
| < 40 | Extremely varied | Modern poetry, creative essays |
| 40–50 | Very rich | Quality literature, editorials |
| 51–60 | Balanced | Serious newspapers, reviews, long articles |
| 61–70 | Moderately simple | Blogs, press releases, speeches |
| 71–80 | Repetitive | Manuals, instructions, simple documents |
| 81–90 | Lexically poor | Advertising, commercial dialogues |
| > 90 | Extremely repetitive | Slogans, robotic texts, voice commands |

## Practical examples

- **K = 38** · Poetic fragment with many images and metaphors, almost no repetitions
- **K = 55** · Balanced editorial, using synonyms and varied expression
- **K = 72** · Press release, with many repetitive verbs and standard structures
- **K = 95** · Advertisement with 3-4 keywords repeated obsessively

## Limits and utility

Yule's K does not measure grammatical or conceptual complexity, but lexical repetition. It is extremely useful for comparing different texts or observing an author's style.

For example:

- **Literary authors** have low K scores (rich vocabulary)
- **Institutional or automated texts** have high K scores

It is often used together with other indicators, such as LIX or TTR (type-token ratio), for a more complete stylistic analysis.

## Key Applications

Yule's K is particularly valuable for:

- **Author attribution**: Different authors have characteristic vocabulary patterns
- **Text classification**: Distinguishing between literary, technical, and commercial texts
- **Quality assessment**: Measuring lexical richness in educational content
- **Automated text detection**: AI-generated texts often have different K patterns than human writing
- **Editorial analysis**: Comparing writing styles across publications

## Understanding the Score

- **Lower scores (< 50)**: Rich, diverse vocabulary with minimal repetition
- **Higher scores (> 80)**: Limited vocabulary with frequent word repetition
- **Inverse relationship**: Unlike readability scores, lower K values indicate higher quality/complexity

## Conclusion

Yule's K is a valuable indicator for those interested in language, writing, creativity, and style analysis. The lower the score, the more vivid, diverse, and pleasant to read the text is. It's a subtle but revealing tool for anyone who wants to better understand how "rich" the language used is.

---

*Source: Translated from [Paradigma.ro - YK](https://www.paradigma.ro/p/yuk)*
