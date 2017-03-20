=== Post Crumbs ===
Contributors: quasarito
Tags: copyright, hash, copying, freeboot, piracy
Requires at least: 3.1.0
Tested up to: 4.7.2
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Creates a signature (crumb) for your posts that can be used to track copied/stolen content.

== Description ==

The Post Crumb plugin will generate a unique signature (crumb) which can be embedded within every post.
This crumb can be used to determine whether your posts have been copied/stolen on the web by searching for the crumb 
on a search engine.

The crumb is generated from the content of the post so each one would be unique, unless there are duplicate
posts, or an (unlikely) "collision" occurs. This plugin should allow you to easily find other websites that have
blatantly copied the contents of your post elsewhere. It should be effective against crawlers and scrapers that copy
content verbatim and republish it. Doing so would have the crumb republished which will be indexed by search engines,
and allow you to search for the crumb to identify websites that have taken your content.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/hhd-pcrumbs` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Tools->Post Crumbs screen to configure the plugin

= Using the shortcode =
You can selectively choose which post and/or the location within a post a crumb appear by using the
shortcode. You can embed the shortcode `[pcrumb]` anywhere in your post. A good place would
be at the very end of your post. This will insert the crumb at the location of the shortcode.
If you want to surround the crumb in an html tag (like `<p>` or `<span>`),
you can specify the optional `tag` attribute with the html element name. There is also
an optional `class` attribute for CSS formatting, if you want to format the crumb differently from
the post content.
Eg: **[pcrumb tag="p" class="smaller"]**

= Enabling Auto-append =
Alternatively, you can have all posts (new and existing) contain a crumb by enabling *auto-append*.
Turning on this option will insert the crumb at the end of every post. There is no need to include a
shortcode in your post if you enable *auto-append*. Optionally, you can have the crumb surrounded with 
an html tag by specifying a **crumb tag**. Use the **crumb class** to include a `class` attribute with
the html tag.

== Upgrade Notice ==

N/A

== Frequently Asked Questions ==

= Can I submit a patch? =

Sure. You can find the code repository here: https://github.com/quasarito/post-crumbs

== Screenshots ==

1. A crumb appended to the end of a post.
2. A crumb added to the end of a page.

== Changelog ==

= 0.170305 =
* Initial version of Post Crumbs.
