=== Media Cleaner ===
Contributors: TigrouMeow
Tags: management, admin, file, files, images, image, media, library, upload, clean, cleaning
Requires at least: 4.2
Tested up to: 4.8
Stable tag: 4.2.3

Clean your Media Library and Uploads directory. It has an internal trash and recovery features. Please read the description.

== Description ==

Clean your Media Library from the media which aren't used in any of your posts, gallery and so on. It features an internal trash, moving the files in there temporarily for you to make sure the files aren't actually in used; once checked, you can trash them permanently. **Before using this plugin, make sure you have a proper backup of your files and database. This is the most important step on the usage of this plugin as you can't trust any file deletion tools.** The Pro version of this plugin brings also scanning to the /uploads folder and will detect which files aren't registered in the Media Library, not used in your content and so on. Retina images are also detected and supported, shortcodes, HTML in sidebars and of course your posts, pages and all post types.

**This tool is a knife. I do my best to make this knife a perfect one. However, this is still a knife, and in the hands of somebody who doesn't know how to use it, it might end badly. Don't use it if you don't have any backup, or if you don't know what it does.**

**PAGE BUILDER**. I am adding support for page builders little by little. Each page builder requires a particular code in the Media Cleaner. As for now, Visual Composer by WPBakery is supported.

**UNIQUE PLUGIN**. Such a plugin is difficult to create and to maintain. If you understand WordPress, you probably know why. This plugin tries its best to help you. Get used to it and you will get awesome results. This is the only plugin to propose those functions and even a dashboard to cleanup your WordPress install from unused files.

**DASHBOARD**. Those file will be shown in a specific dashboard. At this point, it will be up to you to delete them. Files detected as un-used are added to a specific dashboard where you can choose to trash them. They will be then moved to a trash internal to the plugin. After more testing, you can trash them definitely.

**FREE / PRO**. The Free version of the plugin works with the media available in your Media Library. The Pro version adds file scanning to your physical /uploads directory.

**AGAIN, BE CAREFUL**. Again, this plugin deletes files so... be careful! Backup is not only important, it is **necessary**. Don't use this plugin if you don't understand how WordPress works. This is a knife, you need to understand what it does and how before using it.

It has been tested with WP Retina 2x and WPML.

== Installation ==

1. Upload `media-file-cleaner` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go in the Settings -> Media Cleaner and check the appropriate options
3. Go in Media -> Media Cleaner

== Upgrade Notice ==

Replace all the files. Nothing else to do.

== Frequently Asked Questions ==

= Is it safe? =
No! :) How can a plugin that deletes files be 100% safe? ;) I did my best (and will improve it in every way I can) but it is impossible to cover all the cases. On a normal WordPress install it should work perfectly, however other themes and plugins can do whatever they want do and register files in their own way, not always going through the API. I ran it on a few big websites and it performed very well. Make a backup (database + uploads directory) then run it. Again, I insist: BACKUP, BACKUP, BACKUP! Don't come here to complain that it deleted your files, because, yes, it deletes files. The plugin tries its best to help you and it is the only plugin that does it well.

= What is 'Reset' doing exactly? =
It re-creates the Media Cleaner table in the database. You will need to re-run the scan after this.

== Screenshots ==

1. Media -> Media Cleaner

== Changelog ==

= 4.2.3 =
* Fix: Meta search issue.
* Fix: SQL typo for WooCommerce detection.
* Fix: Avoid checking the empty arrays.

= 4.2.0 =
* Info: This is a MAJOR UPDATE both in term of optimization and detection. Keep my motivation up and give a good review to the plugin here: https://wordpress.org/support/plugin/media-cleaner/reviews/?rate=5#new-post. That helps me a lot.
* Add: Support for Fusion Builder (Avada).
* Add: Cache the results found in posts to analyze them much faster later.
* Add: Debugging log file (option).

= 4.1.0 =
* Add: Support for WooCommerce Gallery.
* Add: Support for Visual Composer (Single Image and Gallery).

= 4.0.7 =
* Update: Bulk analyze/prepare galleries, avoid the first request to time out.
* Add: Many option to make the processing faster or slower depending on the server.
* Fix: Handle server timeout.
* Add: Pause button and Retry button.

= 4.0.4 =
* Update: Safest default values.

= 4.0.2 =
* Add: Information about how a certain media is used (Edit Media screen).
* Fix: Check / Create DB process.
* Fix: Plugin was not working well with themes using Background/Header.
* Update: A bit of cleaning.

= 4.0.0 =
* Update: Core was re-organized and cleaned. Ready for nice updates.

= 3.7.0 =
* Fix: Little issue when inserting the serial key for the first time.
* Update: Compliance with the WordPress.org rules, new licensing system.
* Update: Moved assets.
* Info: There will be an important warning showing up during this update. It is an important annoucement.

= 3.6.4 =
* Fix: Plugin was not working properly with broken Media metadata. It now handles it properly.
* Info: If you want to give me a bit of motivation, write a review on https://wordpress.org/support/plugin/media-cleaner/reviews/?rate=5#new-post.

= 3.6.2 =
* Fix: When over 1 GO, was displaying a lower size value.
* Fix: Counting wasn't exact with a Filesystem scan.
* Info: Please read the previous changelog as it didn't appear in WP for some reason.
* Add: Check Posts also look for the Media ID in the classes (more secure).
* Info: If you want to give me a bit of motivation, write a review on https://wordpress.org/support/plugin/media-cleaner/reviews/?rate=5#new-post.

= 3.6.0 =
* Add: Now the Media can be recovered! You can remove your Media through the plugin, make sure they are not in use (by testing your website thoroughly) and later delete them definitely from the trash. I think you will find it awesome.
* Update: Nicer internal icons rather than the old images for the UI.
* Update: Faster and safer for post_content checks.
* Update: This is a big one. The plugin is more clear about what it does. You need to choose either to scan the Media or the Filesystem, and also against what exactly. There has also been a few fixes and it will work on more big installs. If it fails, you can remove a few scanning options, and I will continue to work on making it perfect to support huge installs with all the options on.

= 3.2.8 =
* Update: Show a better edit media screen.
* Update: Will show the same number of items as in the Media Library (before it was fixed to 15 items per page).
* Fix: Was displaying warning if the number of items per page in the Media page is not set.

= 3.2.0 =
* Fix: HTML adapted to WP 4.5.1.
* Fix: Doesn't break if there is an error on the server-side. Display an alert and continue.
* Update: Can select more than one file for non-Pro.
* Fix: Issue with PHP 7.

= 3.0.0 =
* Add: Option for resolving shortcode during analysis.
* Update: French translation. Big thanks to Guillaume (and also for all his testing!).
* Info: New name, fresh start. This plugin changed completely since it very first release :)

= 2.5.0 =
* Add: Delete the unused directories.
* Add: Doesn't break when there are too many files in the system.
* Add: Pro version with better support.
* Update: Improved detection of unused files.
* Fix: UTF8 filenames skipped by default but can be scanned through an option.
* Fix: Really many fixes :)
* Info: Contact me if you have been using the plugin for a long time and love it.

= 2.4.2 =
* Add: Inclusion of gallery post format images.
* Fix: Better gallery URL matching.
* Info: Thanks to syntax53 for those improvements via GitHub (https://github.com/tigroumeow/media-file-cleaner/pull/3). Please review Media Cleaner if you like it. The plugin needs reviews to live. Thank you :) (https://wordpress.org/support/view/plugin-reviews/media-file-cleaner)

= 2.4.0 =
* Fix: Cross site scripting vulnerability fixes.
* Change: Many enhancements and fixes made by Matt (http://www.twistedtek.net/). Please thanks him :)
* Info: Please perform a "Reset" in the plugin dashboard after installing this new version.

= 2.2.6 =
* Fix: Scan for multisite.
* Change: options are now all enabled by default.
* Fix: DB issue avoided trashed files from being deleted permanently.

= 2.0.2 =
* Works with WP 4.
* Gallery support.
* Fix: IGNORE function was... ignored by the scanning process.

= 1.9.0 =
* Add: thumbnails.
* Add: IGNORE function.
* Change: cosmetic changes.
* Add: now detects the custom header and custom background.
* Change: the CSS was updated to fit the new Admin theme.

= 1.7.0 =
* Change: the MEDIA files are now going to the trash but the MEDIA reference in the DB is still removed permanently.
* Stable release.
* Change: Readme.txt.

= 1.4.0 =
* Add: check the meta properties.
* Add: check the 'featured image' properties.
* Fix: keep the trash information when a new scan is started.
* Fix: remove the DB on uninstall, not on desactivate.

= 1.2.2 =
* Add: progress %.
* Fix: issues with apostrophes in filenames.
* Change: UI cleaning.

= 1.2.0 =
* Add: options (scan files / scan media).
* Fix: mkdir issues.
* Change: operations are buffered by 5 (faster).

= 0.1.0 =
* First release.
