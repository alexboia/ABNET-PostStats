# Yule's K

Yule’s K shows how much my vocabulary repeats itself.

## How is Yule's K calculated?

The mathematical formula is:

``K = 10,000 × (M2 - N) / N^2``

Where:

- **N** = total number of words in the text (tokens);
- **M2** = weighted sum of the squares of word frequencies.

The calculation may seem complex, but essentially it's based on how often words are repeated:

- If a word appears **1 time**: it is considered rare;
- If it appears **10 times**: it contributes massively to the K value.

A text where many words are repeated frequently will have a higher K, indicating that the vocabulary is poor or limited.

## Description

Yule’s K measures lexical repetition. A lower value usually means the vocabulary is more varied. A higher value means the same words are carrying a larger share of the text.

This is useful because repetition is not always obvious while writing. I may feel that an argument is progressing, while the language keeps circling around the same verbs, nouns, or stock phrases.

A high score does not automatically mean the article is bad. Technical writing, focused essays, and pieces built around a narrow concept naturally repeat key terms. But if the score is high and the text also feels dull, then the problem may not be the subject. It may be that the language has stopped discovering anything new.

## How I use this to improve

When Yule’s K is high, I look for repeated workhorse words. The goal is not to vandalize the article with random synonyms like a thesaurus goblin on espresso. The goal is to see whether I am repeating words because the idea demands it, or because the sentence machinery got lazy.

A better text does not merely replace repeated words. It sharpens the distinctions between ideas.