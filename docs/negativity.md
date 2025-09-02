# Negativity

**"Negativity" is a simple score that estimates the negative tone of a text by measuring the percentage of negative sentences.**

## What is "negativity"?

"Negativity" or "negative tone" is a simple score that estimates the negative tone of a text by measuring the percentage of negative sentences. It was designed as a stylometric tool that captures the negative tonality of writing without requiring deep semantic analysis.

Unlike traditional sentiment scores (which rely on artificial intelligence or complicated classifications), "negativity" works through a clear and measurable principle: how frequently negations appear in the analyzed text.

## How is it calculated?

The formula is:

**negativity = (number of negative sentences / total number of sentences) × 100**

Where:

- **Sentences** are delimited by periods (`.`), question marks (`?`), exclamation marks (`!`), or a new line.
- **A negative sentence** is one that contains at least one form of negation such as: `nu` (no/not), `n-am` (I don't have), `nici` (neither), `nimic` (nothing), `nimeni` (nobody), `nicicum` (no way), `nicio` (no/none), `niciodata` (never), etc.

For example, if a text has 40 sentences and 8 of them contain clear negations, then:

**N% = (8 / 40) × 100 = 20%**

## How do we interpret the score?

The NEG score is expressed as a percentage between 0% and 100%, but in practice most texts fall between 0% and 40%. Here's an interpretation by deciles, with relevant examples:

| NEG% | Tonality | Text Type | Example |
|------|----------|-----------|---------|
| 0–4% | Extremely positive | Advertisements, motivational stories |
| 5–9% | Positive | Optimistic articles, public presentations |
| 10–14% | Balanced positive | Favorable reviews, warm editorials |
| 15–19% | Neutral | Factual news, administrative texts |
| 20–24% | Moderately negative | Mild criticism, warnings |
| 25–29% | Clearly negative | Complaints, harsh reviews |
| 30–34% | Tense | Social essays, pessimistic analyses |
| 35–39% | Very negative | Pamphlets, protests |
| ≥ 40% | Extremely negative | Fatalistic texts, desperate speeches |

## Practical examples

- **N% = 3%** · Personal success story, without any negative words
- **N% = 12%** · Constructive editorial with some critical observations
- **N% = 26%** · Very dissatisfied movie review
- **N% = 41%** · Manifesto text, with phrases full of frustration and pessimism

## Limits and utility

"Negativity" does not measure emotional subtleties, irony, or sarcasm, but it provides a quick and robust estimate of the general tone. It's especially useful when you want to:

- Compare articles on the same topic
- Observe an author's tendencies
- Filter texts with negative tone in large databases

It can also be correlated with other scores such as LIX (difficulty) or Yule's K (lexical richness) for a more detailed stylometric analysis.

## Conclusion

"Negativity" is a simple but effective tool for quantifying the negative tonality of a text. The higher the score, the more tense, pessimistic, or critical the text is.

It's not a "quality" score, but one of expressed attitude. Ideal for rapid analyses, stylistic classifications, or for detecting tone in journalism, literature, or social media.

---

*Source: Translated from [Paradigma.ro - Negativitate](https://www.paradigma.ro/p/negativitate)*
