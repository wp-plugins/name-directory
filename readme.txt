=== Plugin Name ===
Contributors: jeroenpeters1986
Tags: glossary, index, name, directory, names
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: "trunk"
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=T284NKYDFC8PE&lc=US&item_name=wordpress%2dplugin&item_number=name%2ddirectory&currency_code=USD

This plugin provides a name directory (glossary) with a lot of options. It can be embedded with a simple shortcode and can have multiple directories.

== Description ==

This plugin adds name/term directories to your Wordpress installation. They are like glossaries. The output on your website is like a glossary/index. I recommend you to take a look at the screenshots, they illustrate more than words.

The Name Directory plugin was orginally developed for ParkietenVilla.nl to show a directory of names to name your budgies.

A name directory is a directory that contains entries with the following properties:

 - name
 - description
 - submitter

You can create multiple directories with this plugin. Every directory can be embedded with a very simple shortcode which you can just copy-and-paste in your own pages and posts. Every directory has a few configuration options that customize the layout and functionality of the directory: 

 - Show/Hide title
 - Show/Hide description
 - Show/Hide suggestion form
 - Show/Hide submitter name
 - Show/Hide search function
 - Show/Hide a horizontal rule between the entries
 - Show/Hide all entries when the user has not chosen an index-letter yet
 - When you embed a directory, you can configure it to start with a letter of your choosing. E.g.: start on letter J.

The administration view of this plugin has the familiar look and feel of (the rest of) the Wordpress Administration panel. I have done my best to enable some AJAX-features in the administration panel, so you can work efficiently while adding new entries.

== Installation ==

Installation is very easy. You can just download this plugin through the Plugin Finder in your Wordpress Administration Panel.

If you download the zip-file, it's also very easy: 

1. Unzip the file which results into a directory called `name-directory`
1. Upload that directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create a new name directory and add some names
1. Copy the shortcode and paste it into a post or page to show it on your website

== Frequently Asked Questions ==

= Can I contact you? =

Yes, through this plugin information page.

== Screenshots ==

1. The output of a name directory on a standard Wordpress website. It's a full-featured name directory (search form, index links, descriptions and submit button)
2. Overview of all the name directories in the Wordpress Administration screen for this plugin
3. List of all names in the selected directory and the 'Add name' form
4. Settings screen for a name directory
5. Where to find the Name Directory plugin settings page

== Changelog ==

= 1.6.8 =
 * Added option which let's the user choose a default starting-character when displaying the name directory. For example: use [namedirectory dir="X" start_with="j"] to start with the letter J.
 * WordPress 4.0 compatibility
 * Added Icon to the installer gallery

= 1.6.7 =
 * Updated Russion Translation (Thanks to: Rig Kruger http://rodichi.org)

= 1.6.6 =
 * Fixed small display bug

= 1.6.5 =
 * Showed submitted name

= 1.6.4 =
 * Updated French translations
 * Fixed too-many-slashes issue

= 1.6.3 =
 * Updated Dutch translations
 * Fixed display bug. 
 * The All-link is hidden when you a visitor HAS to choose a letter from the index

= 1.6 =
* Added option 'Show all names by default', this can be disabled to hide all entries if a user hasn't chosen a letter from the index.

= 1.5.2 =
* Fixed bug in CREATE TABLE and backlink in form, thank you very much MerlIAV for the patch!

= 1.5.1 =
* Fixed bug that prevent saving searchform preference in admin

= 1.5 =
* Added search box on front-end (You can enable this in the name-directory settings)
* Added support for four-column layout
* Added Russion Translation (Translated by: Rig Kruger http://rodichi.org)

= 1.4.3 =
* Fixed bug which allowed non-published items to be shown

= 1.4.2 = 
* Fixed support for Chinese characters
* Added French Translation (Translated by: Patrick BARDET http://www.web-studio-creation.fr)

= 1.4.1 = 
* Fixed sorting issue at the frontend

= 1.4 = 
* Added support for HTML in the name description

= 1.3 =
* Name lists can now have multiple columns at the frontend
* Added css in a separate file
* Added database upgrade module

= 1.2.1 =
* Plugin url's are now compatible with third party SEO modules

= 1.2 =
* Added support for submission form on the front-end

* Added possibility for admin to filter on published/unpublished names
* Rearranged directory overview for admin, overview now shows totals for published/unpublished

= 1.1 =
* Added double name detection

= 1.0 =
* First major public release

= 0.5 =
* First version for private use


