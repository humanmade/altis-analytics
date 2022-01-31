# Optimization Framework

Altis Optimization Framework is the set of APIs that builds on top of Altis Native Analytics, to provide the tooling needed to enable Personalization and Experimentation features.

[Altis Experience Blocks](../experience-blocks.md) are built on this framework.

## Experimentation

Experiments are a powerful tool for optimizing content and measuring the effectiveness of changes to the site.

The framework is enabled by default, and provides a friendly developer API for creating custom experiments, as well as some built in A/B tests out of the box.

While the experiments framework is only used for A/B tests at the moment, it is flexible enough to build more types of experiments beyond just that.

[Learn more about A/B testing features and APIs in Altis](./ab-testing.md).

## Personalization

Using Native Analyics, Altis provides the tooling needed to provide personalized content to users of the platform, based on custom audience segments created via the [Audiences feature](../native/audiences.md).

The framework is enabled by default, and provides a built-in _Personalized Content_ Experience Block available in the Block Editor, where you can create different variations of the block and assign each to a specific audience group.

The framework also provides a developer API to that facilitates the use of personalization features outside of the context of the Block Editor as well.

[Learn more about Personalization APIs in Altis](./personalization.md).
