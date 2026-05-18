# LIX

LIX gives me a rough difficulty gauge: how long my sentences are and how many long words I use.

## How is the LIX score calculated?

The formula is:

``LIX = (Total number of words / Total number of sentences) + (Number of long words × 100 / Total number of words)``

Where:

- **Long words** are those that have more than 6 letters;
- **Sentences** are delimited as specified [here](https://github.com/alexboia/ABNET-PostStats/blob/main/docs/processing-info.md).

For example, a text with 200 words, 10 sentences, and 50 long words will have:

``LIX = (200 / 10) + (50 × 100 / 200) = 20 + 25 = 45``

## Description

LIX combines two things that shape readability: sentence length and the proportion of long words. It does not tell me whether a text is good, deep, funny, elegant, or worth reading. It only tells me how much effort the text may demand from the reader.

A higher LIX score can be perfectly acceptable for essays, technical writing, criticism, or theoretical pieces. Some subjects need density. But if the score climbs too high, I should ask whether the difficulty is earned by the idea or merely produced by overloaded phrasing.

A lower LIX score suggests a more accessible text. That can be useful for practical articles, announcements, tutorials, or pieces meant to be read quickly.

## How I use this to improve

If LIX is high, I check whether the text contains too many long sentences, too many abstract nouns, or too many heavy constructions in a row. I do not need to dumb the text down. I need to make sure the reader is climbing a staircase, not a wet wall.

If LIX is low, I check whether the text still has enough texture. Accessibility is good, while thinness might not be.