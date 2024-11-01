=== Bing Custom Search for WordPress ===
Contributors: sbruner, slipfire, freemius
Tags: bing, search, better search, custom search, advanced search
Tested up to: 5.9
Requires at least: 4.6
Stable tag: 2.6.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Improve the search functionality on your site by using Bing Custom Search for WordPress.

<a href="https://channel9.msdn.com/Events/Build/2017/T6021/player?format=html5">Watch the Microsoft video ></a>

= Features: =
* 100% theme compatible: looks like your theme.
* TABBED results (<a href="https://wordpress.org/plugins/piklist/">Requires Piklist plugin</a>)
* Auto-corrects spelling errors in searches.
* Perfect replacement for default WordPress search or Google Custom Search.
* Works with WooCommerce, BuddyPress and bbPress.
* WordPress multisite compatible.
* Get results from multiple sites, or just one.

Learn more:
[youtube https://www.youtube.com/watch?v=wOXwbJhvROI]

== Installation ==
1. Sign up at <a href="https://customsearch.ai/" target="_blank">Microsoft</a>. Bing Custom Search is not a free service. <a href="https://azure.microsoft.com/en-us/pricing/details/cognitive-services/" target="_blank">You can view pricing here</a>.
2. Create a <a href="https://customsearch.ai/applications" target="_blank">new search engine (instance)</a>
3. Refine your new search engine by adding your website, or any website(s) to the search.
4. Click on the "Custom Search Endpoint" button to go to your endpoint page.
5. Make note of your "Primary Key" and "Custom Configuration ID".
6. In your WordPress dashboard, activate "Bing Custom Search for WordPress".
7. Go to the "Bing Search" settings page and fill in all the fields. You will need your "Key and Custom Config" from step 5.
8. Save your settings and start using your search as you normally do.

== Frequently Asked Questions ==

= What does this plugin do? =
This plugin replaces your default WordPress search, with search results from Bing.

= Are there any known issues with certain hosts? =
WP Engine: turn off object caching (the setting can be found in the WP Engine dashboard).

= Do I need to modify my theme or use a shortcode? =
Nope. This plugin takes over the search functionality of your theme. You just have to activate and follow the <a href="https://wordpress.org/plugins/wp-bing-search/#installation">installation instructions<a/> to set up an account at Microsoft.

= I've enabled Bing Statistics but I'm not seeing any data coming through. =
If you have a custom endpoint, use that in the plugin setting screen in place of the default endpoint. 

== Changelog ==

= 2.6.3 =
* Really remove Freemius

= 2.6 =
* Remove Freemius

= 2.4 =
* FIXED: Security fix

= 2.3 =
* UPDATE: Freemius SDK

= 2.2 =
* ENHANCEMENT: Add setting field for custom endpoints

= 2.1.1 =
* UPDATE: Focus query on search results only.

= 2.1.0 =
* ENHANCEMENT: If we have no Bing results return no results.

= 2.0.7 =
* FIXED: Now checks Bing response code properly.

= 2.0.6 =
* Code refactor

= 2.0.5 =
* UPDATE: Freemius SDK

= 2.0.4 =
* UPDATE: fix notice

= 2.0.3 =
* NEW: add uninstall.php
* UPDATE: fix notices

= 2.0.2 =
* UPDATE: Language updates: he_IL, es_ES, fr_FR

= 2.0.1 =
* UPDATE: If Bing error, show default WordPress search.

= 2.0.0 =
* NEW: Use multiple searches with tabs

= 1.0.5 =
* NEW: Add "wp-bing-search" body class when using Bing Custom Search

= 1.0.4 =
* NEW: Integrate Freemius.

= 1.0.3 =
* NEW: load_theme_compatibility function / wp_bing_search_theme_compatibility filter
* NEW: Replace Author name with url host when search results are not local
* UPDATE: Remove altered query from search box
* UPDATE: wp_bing_search::build_endpoint() should run whenever called, not just for "s" query parameter.


= 1.0.2 =
* Better settings page
* Setup instructions on settings page
* Admin notice if plugin not setup properly
* Plugin action link to settings page

= 1.0.1 =
* Do nothing unless settings are filled in.

= 1.0 =
* Initial plugin








