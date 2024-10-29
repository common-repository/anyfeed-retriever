=== AnyFeed Retriever ===
Contributors:Anushkakr
Donate link: http://anushka.pro/plugins/anyfeed/
Tags: feed aggregator, rss feed,atom feed, rss import, atom import, rss parsing, atom parsing, news aggregator
Requires at least: 2.8
Tested up to: 5.1.0
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple, lightweight feed integration plugin which uses simple shortcode to fetch and display any type of feeds using ajax.this plugin supports for rss, rss2 and atom feeds. Use as a news aggregator, autoblog, or feed parsing.

== Description ==

This plugin fetches any feed, or multiple feeds, and displays them in an ordered list using shortcode. And also this plugin can categorized and group feed in to custom category and groups.

<a title="AnyFeed Retriever Demo" href="http://anushka.pro/plugins/anyfeed/demo" target="_blank">**Demo**</a> | <a title="AnyFeed Retriever Tutorial" href="http://anushka.pro/plugins/anyfeed/tutorial" target="_blank">**Video Tutorial**</a>

<h3>How to use:</h3>
01. Add new feed url by creating new Feed post and customize it with category and tags according to your requirement.
02. Simply copy and paste the example code below to wherever you would like to display your feed.

<h3>Example:</h3>
<h4>Method 1</h4>
<pre><code>[anyfeed]</code></pre>
<h4>Method 2</h4>
<pre><code>[anyfeed id="post_id"]</code></pre>
<h4>Method 3</h4>
<pre><code>[anyfeed cat="category_name"]</code></pre>


<h3>Live Demo:</h3>
<p><a title="AnyFeed Retriever Demo" href="http://anushka.pro/plugins/anyfeed/demo/" target="_blank">http://anushka.pro/plugins/anyfeed/demo/</a></p>

<h3>Features:</h3>
<ul>
	<li>Fetch any feeds rss, rss2 or atom</li>
	<li>Display all feeds or group them by using shortcode, including text widgets</li>
	<li>Load feed by using ajax for the performance.</li>
	<li>Simple, lightweight, and fast</li>
	<li>Easy to setup</li>
	<li>Fetch thumbnail or first image or site logo</li>
	<li>Control size of thumbnail (width and height)</li>
	<li>Order items to the published date</li>
	<li>Aggregate multiple feeds into one list</li>
</ul>

<h3>Properties:</h3>
<ul>
	<li><strong>id</strong> - The id of the custom feed post.</li>

	<li><strong>cat</strong> - name of the category</em></li>
</ul>

Please post any issues under the support tab. If you use and like this plugin, please don't forget to <strong>rate</strong> it! Additionally, if you would like to see more features for the plugin, please let me know.

Shortcode can be used anywhere including in posts, pages, text widgets, and in PHP files by using the do_shortcode function. This RSS import plugin is very lightweight with a minimal amount of code as to insure it will not slow down your website. Build a custom news aggregator or use this plugin as a simple feed to post plugin by displaying the RSS parsing feed within the pages of your choice. This RSS aggregator is built on the SimplePie API.

== Frequently Asked Questions ==

= How do I display a feed in my content? =
Create new feed post with your feed url
Select the method you are displaying feeds and copy and paste the example shortcode above into your content.

= How do I display a feed in a widget? =
Create a new text widget. Click on the "Text" tab. Copy and paste the example shortcode above.

= How do I display a feed using PHP? =
Here's an example of how to display an RSS feed with PHP
<pre><code><?php echo do_shortcode('[anyfeed]'); ?></code></pre>

= How do I display a feed with a Gutenberg Block? =
Click on the "+" icon to add a new block. Search for "shortcode". Click on the shortcode block to add it. Copy and paste the example shortcode above into the block.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `anyfeed.zip` to the `/wp-content/plugins/` directory
2. Unzip the file
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Use the example shortcode [anyfeed] anywhere in your content
5. Change the id and other properties as needed


== Changelog ==

= 1.0 =
* Initial release

= 1.0.1 =
* Facilitate add feed category by a shortcode [anyfeed-cat]
* Changing from date and time to Elapsed Time Ex : '15min ago ,2 hours ago, 1 day ago etc'
* Limit the number of words that each article displays on the feed , Max 40 words.