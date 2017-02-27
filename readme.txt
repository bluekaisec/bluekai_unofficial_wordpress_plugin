=== Plugin Name ===
Contributors: Roshan Gonsalkorale, Ian Hampton
Tags: bluekai
Donate link: http://www.unofficialbluekai.com
Requires at least: 3.0.1
Tested up to: 4.7.2
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds the BlueKai tag and declares data points (phints)

== Credit == 
Based on Ian Hampton's Tealium WordPress plug-in https://en-gb.wordpress.org/plugins/tealium/

== Description ==

= Features =

Allows users to easily add the BlueKai tag without editing any template files. 

The following phints are declared

* Site name
* Site description
* Post date
* Post categories
* Post tags
* All post meta data, including custom fields
* Search terms
* Number of search results


= About BlueKai =

BlueKai is a DMP

== Installation ==

To install:

* Install from the Wordpress plugin repository, or copy to your wp-content/plugins folder.
* Enable through the plugins section within Wordpress.
* Paste your BlueKai code into 'BlueKai Settings' under Settings in Wordpress and save.
* That's it!

Optional steps:

* If there are phints you wish to exclude from your data tag, add the keys as a comma separated list.

== Frequently Asked Questions ==

= What data is currently included in the data object? =

* Site name
* Site description
* Post date
* Post categories
* Post tags
* All post meta data, including custom fields
* Search terms
* Number of search results

= Can I add to the phints dynamically using PHP code? =

This can be achieved calling the 'bluekai_addToDataObject' action from your themes functions.php file or a separate plugin.

== Screenshots ==

== Changelog ==

= 0.1 =
Initial version

=== Credits ===
