# Hapax Legomena

In linguistics and stylometry, hapax legomena are words that appear only once in a text.

The term comes from ancient Greek: hapax (once) + legomenon (what is said). It is the plural of hapax legomenon and refers to those unique, unrepeatable words within a work or corpus.

The frequency of hapax words offers a clear perspective on:

- **Lexical richness**
- **Vocabulary originality** 
- **Degree of repetition or redundancy**
- **Author's style** (formal vs creative, technical vs literary)

In stylometric analysis, hapax words function as a kind of "lexical fingerprint" - the more words an author uses only once, the more varied and less repetitive their writing is.

## Measurement Methods

There are several ways to use hapax words in analysis:

### 1. Simple Count
How many words appear only once in a given text? (e.g., 182 hapax words)

This is an absolute value, useful only in relation to text size.

### 2. Hapax / Token Ratio
Shows how "rare" words are in the general flow of the text. It is strongly influenced by text length.

### 3. Hapax / Types Ratio
Shows what percentage of vocabulary is used only once. It is independent of text length and faithfully reflects lexical variety. **This is the score we use here.**

### 4. Honoré Index (R)
**R = 100 × ln(N) / (1 - V₁/V)**

Where:
- **N** = total words (tokens)
- **V** = unique words (types) 
- **V₁** = hapax words

This is a more complex formula that combines rarity with text length. Values can vary greatly and are harder to interpret intuitively.

## Why We Chose the Hapax/Types Ratio (Hapax-to-Types)

- **Easy to interpret** (values between 0 and 100)
- **Stable** - doesn't "explode" based on length
- **Provides clear measure** of vocabulary diversity
- **Allows direct comparisons** between texts of different sizes

## Interpretive Examples

| Hapax (%) | Interpretation |
|-----------|----------------|
| Under 40% | Repetitive, formal, template-like text |
| 50–60% | Balance between variety and coherence |
| 65–75% | Diverse vocabulary, personal style |
| Over 75% | Exploratory, poetic, free style |

## Formula

**HTR = (V₁ / V) × 100**

Where:
- **V₁** = number of hapax legomena (words appearing exactly once)
- **V** = total number of unique words (vocabulary size)

## Applications

The Hapax-to-Types Ratio is particularly valuable for:

- **Author attribution**: Different authors have characteristic hapax patterns
- **Style analysis**: Distinguishing between formal and creative writing
- **Text classification**: Identifying technical vs. literary content
- **Quality assessment**: Measuring vocabulary richness in educational materials
- **Comparative analysis**: Evaluating lexical diversity across different texts

## Advanced Applications

There are many other ways hapax legomena could be used for analysis, such as:

- **Positional analysis**: Tracking where hapax words appear more frequently (introduction, conclusion, etc.)
- **Statistical distribution**: Measuring whether they have uniform distribution or are random
- **Temporal analysis**: Observing how hapax usage changes throughout a text

However, for the purpose of general and intuitive information, the percentage of words that appear only once out of the total unique words is sufficient.

## Key Insights

- **Higher percentages** indicate more experimental, creative writing styles
- **Lower percentages** suggest more controlled, repetitive, or formal writing
- **Independent of text length**, making it ideal for comparative analysis
- **Complements other metrics** like Yule's K and Type-Token Ratio for comprehensive stylometric analysis

---

*Source: Translated from [Paradigma.ro - Hapax legomena](https://www.paradigma.ro/p/hapax)*
