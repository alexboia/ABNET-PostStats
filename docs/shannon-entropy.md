# Entropy

Entropy shows how predictable or varied the language is.

## How is it calculated?

For a text containing $n$ unique types of words or characters, each with relative frequency $p_i$, entropy is calculated as:

$$
H = -\sum_{i=1}^{n} p_i \log_2(p_i)
$$

Where:

- $H$ is entropy
- $p_i$ is the probability (relative frequency) of the $i$-th unit
- the logarithm is base 2, so entropy is measured in bits

The more uniform the distribution (all words appearing at roughly the same rate), the higher the entropy.

This plug-in uses word-level entryopy.

## Description 

Entropy measures how evenly words are distributed across the text. A higher score usually means the language is more varied and less predictable. A lower score usually means the text repeats the same words or structures more often.

I treat entropy as a signal of textual movement. If the score is low, the text may be too repetitive, too template-like, or too dependent on a limited set of expressions. If the score is high, the text may feel rich, energetic, and surprising.

But very high entropy is not automatically a virtue. A text can become so varied that it loses coherence. Not every paragraph needs to arrive dressed as a baroque chandelier.

## How I use this to improve

When entropy is low, I look for repeated structures, repeated openings, and repeated conceptual loops. The question is: am I developing the idea, or am I merely restating it?

When entropy is very high, I check whether the article still has a clear spine. Variation is good when it serves direction. Otherwise, it becomes wandering.