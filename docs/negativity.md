# Negativity

Negativity shows how often the text uses explicit negation.

## How is it calculated?

The formula is:

``Negativity = (Number of negative sentences) / (Total number of sentences) × 100``

Where:

- **Sentences** are delimited as specified [here](https://github.com/alexboia/ABNET-PostStats/blob/main/docs/processing-info.md);
- **A negative sentence** is one that contains at least one form of negation as defined in the plug-in settings.

For example, if a text has 40 sentences and 8 of them contain clear negations, then:

``N% = (8 / 40) × 100 = 20%``

## Description

This score estimates negative tone by counting sentences that contain forms of negation. It does not understand irony, sarcasm, nuance, or moral outrage performed with a velvet glove. It simply tells me how often the text says no, not, never, nothing, nobody, or similar things.

A higher score suggests a more critical, tense, defensive, corrective, or pessimistic text. That may be exactly right for polemics, reviews, warnings, or essays about things that richly deserve a slap with the wet newspaper of reason.

A lower score suggests a more affirmative or neutral text. That can be useful when the article aims to explain, guide, invite, or build.

## How I use this to improve

When negativity is high, I ask whether the article is merely rejecting things or actually building an alternative. A strong critique should not only say what is wrong. It should also make the reader see what would be better.

When negativity is low, I check whether the text is avoiding conflict too politely. Some articles need a little bite. The trick is to make the bite accurate, not just loud.