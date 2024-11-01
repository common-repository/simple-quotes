=== Plugin Name ===
Contributors: jernst
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=JBMA76TCQA4XL&lc=CA&item_name=Jason%20Ernst&currency_code=CAD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: quotes, simple, author, flexible, widget
Requires at least: 3.0
Tested up to: 3.9
Stable tag: 2.0

Just a simple project that flexibly prints random quotes anywhere on a wordpress page. Works with or without widgets. Comes with an options pages that lets you style the quotes displayed on in your template. As simple a one line addition to most templates.

== Description ==

<p>Just a simple project that prints random quotes anywhere on a wordpress page. Essentially it is just a custom post type that includes the quote and the author. A single function call returns the random quote which can then be displayed wherever you like on your pages.</p>

<p>To use the plugin just add the quotes using the WordPress admin interface on your site. Then to get the quotes to display on the site, call quote_dispaly_random() to display a default styling or the quote_random() function which will return an associative array with both the content, author and date in it. For more details see <a href="http://www.jasonernst.com/projects/quotes/">www.jasonernst.com/projects/quotes/</a> for more details.</p>

<p><strong>Update</strong>: As of version 1.05 it is also possible to use the widget system to display a random quote. You can style the quote by creating css for the "quote" class.</p>

<p>The reason why this format was chosen, was to create the most flexibility for use in a template. It is easy to stick these inside of a div or apply css styling to it.</p>

== Installation ==

1. Upload `simple-quotes` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php quote_display_random(); ?>` in your template to have the default quote style displayed or
1. Alternatively, place `<?php $quote = quote_random(); echo $quote['content']; echo $quote['author']; echo $quote['date']; ?>` in your templates if you would like to style the quote yourself
1. Or use the widget tool and drag the simple quotes widget to where you would like it in your template

== Frequently Asked Questions == 

= How do I get a quote to display in my template under the header =

Look for your templates header.php file. Inside this file look for a section with something like this `<?php bloginfo('name'); ?>`. You can usually insert `<?php quote_display_random(); ?>` somewhere near here to get it to display in your template. Altneratively, if your template supports widgets in the header you can use the wdiget system to add simple quotes to your template.

== Changelog ==

= 2.0 =
* Re-implemented the plugin, removed some of the function which have been deprecated in previous releases so that the plugin maintains compatibility in the future. Designed for version 3.9 of Wordpress. Added the ablity to give the widget a title. Added a new configuration option to give the quote a background color (requested by a user).

= 1.07 =
* No real changes as of this version, just re-uploaded and double-checked it still works to keep it in the public repos. Tested with a fresh install of WP 3.8.1 and it is still working fine.

= 1.06 =
* The look of the quote is now customizable with an option page that has been added to the admin interface. It is now possible to hide the author and date, change the alignments (left or right), change the color of the quote and whether or not to make it italic or bold.

= 1.05 =
* Added support for a simple widget which displays a random quote. More control over the look of the widget to come...

= 1.04 =
* Added quote title so that the content can be placed inside a normal content field (as suggested by Tim - http://www.jasonernst.com/projects/quotes/comment-page-1/#comment-5045)
* Now quotes may contain links, bold, italic and other markup just the same as any other post
* Titles are often shorter than the quote itself resulting in shorter URLs in the case where pretty permalinks are enabled
* NOTE: You can now either enter the quote in the title field or in the content field, if the content is empty it will use the title

= 1.03 =
* Fixed a problem where the date was not being saved correctly

= 1.02 =
* Added an optional date field for the quotes
* Added a function `quote_display_random()` which will display a default styled quote to make it easier to use

= 1.01 =
* Changed the method for getting the title to the_title() since the previous method does not seem to work in WP3.1

== Upgrade Notice ==

== Screenshots ==
1. An example of the quotes displayed on a page header
2. The administrative interface for a adding/editing quote
3. The options page for configuration the look of the quote
4. The list of all available quotes
