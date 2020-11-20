# IndieWeb Custom Post Types
Use short-form post types to IndieWebify your WordPress site

## TL;DR
Compared to solutions that rely on custom taxonomies (only), this plugin (1) allows for maximum flexibility, and (2) leads to a greatly decluttered WP Admin Posts screen.

It does, however, require _some_ knowledge of HTML and Microformats (namely, [microformats2](http://microformats.org/wiki/h-entry)). (Microformats blocks may eventually solve this.)

Works best with the IndieAuth and Micropub plugins, and a microformats-aware theme.

## How this works
IndieWeb Custom Post Types does exactly 5 things:
- Register short-form CPTs
- Hook into the Micropub plugin to use these CPTs
- Automatically generate titles for most short-form content (you'll probably want to hide these on your site's front end, though)
- Create random slugs for short-form content
- Remove the `title` attribute from most short-form RSS items

And the good thing: **all of these can be disabled**, too.

## The CPTs, and Microformats
This plugin registers both a **Note** and a **Like** CPT. (At this time, it does not separately define things like Replies, Bookmarks, RSVPs, etc.) These, on their own, don't really do much.

Nevertheless, you'll find that using just a tiny bit of in-post markup, you _are_ able to create, as far as _Microformats parsers_ are concerned, all of the post types below (and probably more), **on the fly**:
- 📄 article
- 📔 note
- 👍 like
- 💬 reply
- 🔖 bookmark
- 📷 photo

_Somewhat unfortunately, adding custom classes to inline elements like hyperlinks requires you hop into either the "Edit as HTML" (Gutenberg) or "Text" (Classic Editor) view when editing a post. That said, HTML is fun, and powerful!_

## Micropub
If you're going to be posting the bulk of your microblog content (i.e., Notes and other post types that extend from them) through **Micropub** and the Micropub plugin for WordPress, you're in luck!

The Micropub plugin already adds the necessary in-post HTML (all filterable, of course), and this plugin takes it from there.

That is, Likes will end up in WP Admin's Likes section, and Notes (and the other types above, _except Articles_) under, uh, Notes. Neat!

## Filters!
Filter hooks allow you to completely modify or undo (part of) this plugin's behavior.

_Side note, but an important one: none of this applies to "long-form" content (or replies, or anything), especially when it **wasn't** posted through Micropub! That is, **actual** WordPress posts (Articles, in IndieWeb jargon) that happen to contain, e.g., a backlink with a `u-bookmark-of` class aren't at all affected by this plugin. They'll show up in your Article feeds, and Microformats parsers will consider them a bookmark, [like they should](https://www.w3.org/TR/post-type-discovery/)._

From that last link (emphasis mine):
> Post Type Discovery specifies algorithms for determining the type of a post by what properties it has and potentially what value(s) they have, which _helps avoid the need for explicit post types_ that are being abandoned by modern post creation UIs.

You shouldn't have to explicitly assign a certain taxonomy (or post type) for a post to "double" as a reply, bookmark, or what not. The Article–Note distinction (i.e., title vs. no title), though, can be quite useful (I find).

### Autogenerate (and hide) bookmark titles
Short-form content is often titleless. This plugin, however, autogenerates Note (and Like) titles that greatly simplify browsing WP Admin.

Your theme will have to make sure they're correctly ["hidden" on the front end](http://microformats.org/wiki/h-entry#p-name_of_a_note), though! (Note titles accidently given a `p-name` class will cause Microformats parsers to treat Notes like Articles, and something similar goes for Notes in RSS.)

The most common RSS feeds, however, are taken care of by this plugin: Note titles are automatically scrubbed from them. (That is, if you're not already using a modified RSS template.)

Bookmarks are a common exception and are often given the title of the _actually bookmarked page_, and that's why this plugin will _not_ treat them like other "Notes." (This, too, can be overriden using the `iwcpt_ignore_bookmark_titles` filter.)

### Treat titles entirely differently
In case you want to roll your own autogenerated titles:
```
add_filter( 'iwcpt_title', function( $filtered_title, $original_title, $post_content ) {
    // Use, e.g., `$post_content` to completely modify `$filtered_title`.
    // `$original_title` holds the title, if any, that was submitted using Micropub, or filled out in WP Admin when editing the post.
    return $filtered_title;
}, 10, 3 );
```

### Altogether remove automatic titles
Easy! Add `remove_filter( 'wp_insert_post_data', array( IWCPT\IWCPT::get_instance(), 'set_title' ), 10 );` to, e.g., your theme's `functions.php`.

### Do not autogenerate slugs
This plugin will autogenerate random slugs for the CPTs it introduces. If you'd rather set slugs by hand, or go with WordPress's default behavior, use `remove_filter( 'wp_insert_post_data', array( IWCPT\IWCPT::get_instance(), 'set_slug' ), 11 );`.
