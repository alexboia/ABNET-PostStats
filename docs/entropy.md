# Entropy

## What is entropy in stylometry?

In stylometry, entropy measures the diversity and unpredictability of the language used in a text. It indicates how much new information each unit (word or character) adds, and how predictable their distribution is.

A text with high entropy uses a varied vocabulary and avoids repetition. A text with low entropy repeats the same words or structures many times, becoming predictable and lexically poor.

## What is Shannon entropy?

Shannon entropy, introduced in information theory, is the standard formula used to measure uncertainty in a message. It is also used in text analysis to evaluate how uniformly words or characters are distributed in a text.

## Calculation formula

For a text containing $n$ unique types of words or characters, each with relative frequency $p_i$, entropy is calculated as:

$$
H = -\sum_{i=1}^{n} p_i \log_2(p_i)
$$

Where:

- $H$ is entropy
- $p_i$ is the probability (relative frequency) of the $i$-th unit
- the logarithm is base 2, so entropy is measured in bits

The more uniform the distribution (all words appearing at roughly the same rate), the higher the entropy.

## Character entropy vs. word entropy

- **Character entropy** analyzes the diversity of letters and punctuation marks. It can reflect writing style (for example, a fragmented or poetic style).
- **Word entropy** is more relevant in stylometry, showing how varied the vocabulary is. This version is generally used to compare authors or stylistic tones.

## Interpreting the entropy score (`ent 1-10`)

In this stylometric system, the score is scaled from 1 to 10:

| `ent` score | Meaning |
|---|---|
| 1-3 | Repetitive, predictable style; poor lexical variety |
| 4-6 | Balanced, common style; moderate vocabulary |
| 7-8 | Expressive style; rich vocabulary; high variation |
| 9-10 | Dense, sophisticated style; sometimes cryptic |

Entropy (`ent`) measures how varied the language is.

- A high score means the text has rich vocabulary and balanced word distribution.
- A low score indicates repetition and a predictable style.
- More uniform distribution = higher entropy.
