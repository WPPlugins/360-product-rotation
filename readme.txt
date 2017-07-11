=== 360 Product Rotation ===
Contributors: YoFLA
Tags: 360, 360 product view, 360 product rotation, 360 product viewer, 3d product viewer, 360 view software,
product rotation, objectvr, object vr, 3D product rotation, 3D, product spin, 360 product spin
Requires at least: 3.3.0
Tested up to: 4.8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.3.2

Turn series of product photos into an interactive 360 degree view.

== Description ==
####Demo####
An example is worth 1000s words: [View Online Demo](https://www.yofla.com/3d-rotate/wordpress-integration-demo/)

####Works in 3 steps ####
1. Grab this [sample archive](https://www.yofla.com/3d-rotate/wordpress/wp-content/uploads/yofla/demo_backpack.zip) or shoot your own [product images](https://www.yofla.com/3d-rotate/support/manuals/how-to-photograph-a-product-for-360-degree-product-photography/)
2. Upload the archive in the WP Admin&raquo; Media&raquo;360 Views
3. Copy and paste the short code into any page or post

If you want to have more control over your 360&deg; view, you can use the free [3DRT Setup Utility](https://www.yofla.com/3d-rotate/download/) to generate your 360° product view.


####Features####
* Full 360° view
* Responsive design
* Works on mobile devices
* WooCommerce support (PRO)
* Multiple levels (PRO)
* Zooming (PRO)
* Hotspots (PRO)


== Installation ==
* Install 360 Product Rotation Plugin by installing from your Wordpress Admin area
* Activate the module via the Plugins page in your Wordpress Admin area
* Upload the archive with images in WP Admin&raquo; Media&raquo;360 View
* Use a shortcode to embed:

[360 width="100%" height="375px" src="yofla360/weddingring"]

* **src** is the product folder name under *wp-content/uploads*. (this is generated for you)
* **width** is your desired width in px or % (optional parameter, defaults to 500px)
* **height** is your desired height in px or % (optional parameter, defaults to 375px)

**Creating a 360&deg; archive file for upload:**

1. You can zip your images into an archive and upload it. Optionally you can add a settings.ini file like in [demo_backpack.zip](https://www.yofla.com/3d-rotate/wordpress/wp-content/uploads/yofla/demo_backpack.zip) to fine-tune the rotation options.
2. Or you can zip and upload the folder the [3DRT Setup Utility](https://www.yofla.com/3d-rotate/download/) outputs.

**Shortcode options:**

List of all available shortcode options here: [360&deg; WP Plugin Manual](http://www.yofla.com/3d-rotate/support/plugins/wordpress-plugin-360-product-rotation/)

== Screenshots ==
1. This is how the player (pro version) looks like. It is possible to customize the theme (buttons)
2. Uploading an new 360 view is easy
3. Optionally you can use the desktop application [3DRT Setup Utility](https://www.yofla.com/3d-rotate/download/) for more customization



== Changelog ==
#####1.3.2#####
* Feat: Added WooCommerce support for variable products

#####1.3.1#####
* Fix: BugFix in uploading .zip files creted by 3DRT Setup Utility
* Feat: Improved error reporting when using 360 shortcode with invalid path

#####1.3.0#####
* Minor bugfix (WooCommerce not-360-view Product Gallery Fix)

#####1.2.9#####
* Minor bugfix

#####1.2.8#####
* Fix for WooCommerce 3.0 - replacing main product image now works

#####1.2.7#####
* Folder list sorted by name

#####1.2.6#####
* Smaller UI updates (for .zip upload)

#####1.2.5#####
* Enabling .zip upload and fixing security issues. A must upgrade if you are using 1.2.3 or older version.

#####1.2.4#####
* Disabling file upload, important upgrade.

#####1.2.3#####
* Bugfix in specifying custom themUrl parameter in settings.ini file (used for "just images" upload)

#####1.2.2#####
* Added the option to specify own global location for the player engine file (rotatetool.js)
* Added the option to make the 360 views user their own (local) player engine file (rotatetool.js)
* Added the option to specify custom theme url in settings.ini file (used for "just images" upload)

#####1.2.1#####
* Bugfix: .zip file created on some Windows systems did not extract correctly

#####1.2.0#####
* Bugfix: using dash in folder name caused an error

#####1.1.9#####
* Possible SSL communication bug fix when entering license-id in settings

#####1.1.8#####
* Nicer error reporting when processing of uploaded .zip archive fails

#####1.1.7#####
* Bug fixed for using correct (licensed) rotatetool.js file for "just images upload"

#####1.1.6#####
* Bug fixed when uploading zip file

#####1.1.5#####
* Bug fixed when using License Key and not License ID in settings.

#####1.1.4#####
* WooCommerce integration added. Google Analytics support improved. Plugin code refactored & cleaned up. Many under-the-hood improvements.

#####1.1.3#####
* Added the option to upload product via WordPress Interface - now you do not need an FTP client for uploading a 360 view

#####1.1.2#####
* Improvements in creating 360 views by just uploading the images: [example](https://www.yofla.com/3d-rotate/examples/tank-model/).

#####1.1.1#####
* Updated to support SSL on websites
* Improved "just images" functionality for better support of local settings.ini

#####1.0.9#####
* Small code improvement for better updating from older versions of the plugin (creates yofla360 folder in uploads dir)

#####1.0.8#####
* Added support for creating 360 product views from images only (no need to use 3DRT Setup Utility)

#####1.0.7#####
* Added support for specifying Absolute URLs as src parameter, e.g. for amazon s3 files hosting

#####1.0.6#####
* Added support for Google Analytics Events Tracking

#####1.0.5#####
* typo in 1.0.4 fixed

#####1.0.4#####
* temporary disabled ssl connection for cloud based rotatetool.js (problem with renewing ssl certficate on side of my hosting provider)

#####1.0.3#####
* iframe embed mode is now turned on by default (for better fullscreen support)
* added option to set default iframe styles in Settings page

#####1.0.2#####
* added error message when user wants to embed one object in one page twice or more (what is not currently possible)
* added support for popup embed mode
* fixed bug when using px values

#####1.0.1#####
* added support for embedding flash based 360 product rotations created with 3DRT Setup Utility 1.3.8 and older

#####1.0.0#####
* initial release

== Frequently Asked Questions ==

= Does it work on mobile devices? =

Yes, the 360&deg; player works on iOS/Android and other touch and mobile devices.

= Is it possible to customize the buttons? =

Yes, the buttons are just images that you can replace with your own. If you know css, you can edit a styles.css file to achieve a look of the player exactly as you like.

= How much product photos do I need? =

It is recommend at least 36 photos for a smooth rotation - one shot at each 10&deg; degrees for a full 360&deg; view.

= What is the settings.ini in the demo_backpack.zip for? =

You can create a 360&deg; product view just by uploading the product images (so you do not need to install or run the 3DRT Setup Utility).
The settings.ini file specifies options like speed of rotation, type of preloader and others. Please checks this [example on using settings.ini file](https://www.yofla.com/3d-rotate/examples/tank-model/)


== Upgrade Notice ==

= 1.1.3 =
Adds the option to upload a 360&deg; view from within WP admin zone. Now you do not need to use a FTP client.
