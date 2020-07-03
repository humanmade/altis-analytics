# Experience Blocks

Altis [Experience Blocks](https://www.altis-dxp.com/experience-blocks/) are built-in blocks for the editor that provide advanced capabilities. We are continually extending the range and uses of these blocks.

The blocks themselves are backed by analytics data and [audiences](./audiences.md) making it possible to record interactions and impressions with specific pieces of content on your pages. This document outlines the underlying mechanism so that you can implement these blocks outside of the scope of the block editor.

## Personalization

Because it's crucial that web pages can be fully cached for performance we provide [web components](https://developer.mozilla.org/en-US/docs/Web/Web_Components) that are simple to use and load immediately as the document is loaded.

The web component `<personalization-block>` can be used anywhere on your pages to show content conditionally according to the audience segments the current website visitor matches.

The markup makes use of the `<template>` tag along with the web component and data attributes. `<template>` tags can contain any markup however that markup will remain inert until the contents are cloned into another DOM node. This means images, iframes and script tags for example will not try to load any resources until they are cloned.

```html
<template data-audience="1" data-parent-id="abcdef">
	<p>Content to show to audience 1</p>
</template>
<template data-audience="2" data-parent-id="abcdef">
	<p>Content to show to audience 2</p>
</template>
<template data-fallback data-parent-id="abcdef">
	<p>Fallback content for everyone else</p>
</template>
<!-- The HTML tag can not be self-closing. -->
<personalization-block client-id="abcdef"></personalization-block>
```

- The `<personalization-block>` must have a unique `client-id` attribute.
- The `<personalization-block>` must come _after_ the associated templates.
- The `<template>` tags `data-parent-id` must match the `<personalization-block>` `client-id` attribute.
- A `<template>` tag with a `data-fallback` attribute can be optionally added to provide default content.
- The `<template>` tags must have either a `data-audience` attribute with the audience ID as a value or a `data-fallback` attribute to be used.

The audiences a visitor falls into are determined automatically on each visit to your site. The audience IDs they belong to are accessible using the following JavaScript:

```js
// Array of audience IDs.
const audiences = Altis.Analytics.getAudiences();
```

If the visitor's audiences are updated for any reason, for example a `registerAttribute()`  or `updateEndpoint()` call results in a change to the data then an update event is fired that can be subscribed to as follows:

```js
Altis.Analytics.on( 'updateAudiences', function ( audiences ) {
	console.log( 'New audiences', audiences );
} );
```

This method is used to update the `<personalization-block>` tags automatically in the event of a visitor's audiences changing.
