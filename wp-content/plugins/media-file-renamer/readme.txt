=== Media File Renamer (Auto Rename) ===
Contributors: TigrouMeow, amekusa
Tags: rename, seo, file, files, media, renamer, optimize, library
Requires at least: 4.8
Tested up to: 5.1
Requires PHP: 7.0
Stable tag: 4.5.4

Physically renames your files nicely for a cleaner system and for a better SEO. Please read the description.

== Description ==

Media File Renamer Auto is a WordPress plugin that physically renames media files nicely for a cleaner system and for a better SEO. Please read this description.

=== IMPORTANT ===
This is an *automatic* renamer based on the Media title. This plugin features were all meant to be automatic depending on the title of the Media. Manual Rename (and a few more features) were added two years later, in a Pro version. I add complex features based on requests usually in the Pro to be able to maintain the quality of the plugin and its support.

=== HOW IT WORKS ===
The plugin automatically renames your media filenames depending on their titles. When files are renamed, the references to it are also updated (posts, pages, custom types and their metadata). A new column in the Media Library will display to you the new ideal filename and a button will allow you to rename it straight away. You can lock and unlock the renaming automatic process through little icons. There is also a little dashboard called File Renamer in Media that will help you rename all your files at once. Advanced users can change the way the files are renamed by using the plugin's filters (check the FAQ). There is also a LOCK option on every image to avoid the filename to be modified any further.

[youtube https://youtu.be/RjaA-b3avxs]

=== PRO VERSION ===
The [Pro Version](https://meowapps.com/media-file-renamer/) gives a few more features like manual renaming, renaming depending on the post the media is attached to or the content of the alternative text (ALT), logging of SQL queries and a few more options. A good process is to actually let the plugin do the renaming automatically (like in the free version) and to do manual renaming for the files that require fine tuning.

=== BE CAREFUL ===
File renaming is a dangerous process. Before renaming everything automatically, try to rename a few files first and check carefully that all the references to those files have been properly updated on your website. WordPress has so many themes and plugins that this renaming process can't unfortunately cover all the cases, especially if other plugins are using unconventional ways. If references aren't updated properly, please write a nice post (not an angry one) in the support threads :) I will try my best to cover more and more special cases. In any case, always make a **backup** of your database and files before using a plugin that alter your install. For backup, I recommend use the excellent service called [BlogVault](https://meow.click/blogvault). Also, if your website seems broken after a few renames, try to **clear your cache**. The cached HTML will indeed not be linked to the new filenames.

=== FOR DEVELOPER ===
The plugin can be tweaked and reference updates enhanced for your themes/plugins. Have a look [here](https://meowapps.com/media-file-renamer-faq/).

=== JUST TO MAKE SURE ===
This plugin will not allow you to change the filename *manually* in its standard version. The [Pro Version](https://meowapps.com/media-file-renamer/) is required. If you are about to *write an angry review* because this feature is not available, at least, please *mention that you read the whole description*.

This plugin works perfectly with WP Retina 2x, Polylang, WPML and many more. Is has been tested in Windows, Linux and OSX systems.

Languages: English, French.

== Installation ==

1. Upload `media-file-renamer.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Try it with one file first! :)

== Frequently Asked Questions ==

Check the FAQ on the official website, [here](https://meowapps.com/media-file-renamer/faq/).

== Screenshots ==

1. Type in the name of your media, that is all.
2. Special screen for bulk actions.
3. This needs to be renamed.
4. The little lock and unlock icons.
5. Options for the automatic renaming (there are more options than just this).

== Changelog ==

= 4.5.4 =
* Fix: Search was sometimes not working properly in the Media Library.

= 4.5.2 =
* Update: Code cleaning, Youtube video.
* Fix: Issue with updating the ALT field.

= 4.4.0 =
* Update: Compatibility with WP 5.0.
* Update: Compatibility with Real Media Library.

= 4.2.8 =
* Fix: Better support for Real Media Library.
* Update: Improved transliteration.

= 4.2.7 =
* Fix: Removed Updraft.
* Update: UTF-8 is handled by default, no need to have an option for it.
* Add: Option for transliteration (cyrillic, accents, umlauts).
* Note: If you like it, please review the plugin here: https://wordpress.org/support/plugin/media-file-renamer/reviews/?rate=5#new-post. It's important for us :) Thank you!

= 4.2.2 =
* Add: Polylang compatibility.
* Update: UI enhancements and attempt to make the renaming faster.

= 4.2.1 =
* Add: All the actions in the Media Library are now asynchronous. No more page reload!
* Update: Many changes and little enhancements in the code, for speed, security and code-tidiness.

= 4.0.4 =
* Fix: Renaming using filters (work in progress).
* Fix: Insensitive-case match was giving the wrong file in some cases (webp for instance).

= 4.0.2 =
* Fix: PDF thumbnails support.
* Update: Code improvement, faster SQL queries.

= 4.0.1 =
* Fix: Issue with the tolowercase feature.
* Fix: Extension issue with mfrh_new_filename filter.
* Add: Filter to rewrite Alt Text.

= 4.0.0 =
* Update: Huge code cleaning and major refactorization. The core was also rewritten.
* Add: Compatibility with Beaver Builder.
* Fix: Avoid looking for too much perfection (which is dangerous) when using numbered files.
* Fix: Works fine now with image sizes in the meta which has the... same size.

= 3.7.2 =
* Update: Now uploading Media into Post rename the filename accordingly.

= 3.7.1 =
* Fix: Rename on Upload issue in a few cases.

= 3.7.0 =
* Update: Improved Rename on Upload.
* Fix: Annoying warning (but it was not causing any error).

= 3.6.9 =
* Update: Manual Rename allows a new extension.

= 3.6.8 =
* Add: Little API.

= 3.6.7 =
* Add: Bulk rename in the Media Library.

= 3.6.6 =
* Add: Table with the filenames before and after renaming + CSV Export (works with Redirection plugin).

= 3.6.4 =
* Add: Button "Undo All" to restore all the original filenames.
* Fix: Avoid the Numbered Files and Force Renamed options to be activated at the same time.

= 3.6.0 =
* Fix: Compatibility with WPML.
* Fix: There was a compatibility issue with retina.

= 3.5.8 =
* Add: New button "Lock All"
* Fix: Button "Unlock All and Rename" was not really unlocking everything.

= 3.5.6 =
* Fix: Sometimes numbered files were renamed something like abc-2-2.jpg.
* Update: Rename with lowercase to avoid issues.
* Add: Option for Pro, Media Title is synchronized with Title of Attached Post.

= 3.5.4 =
* Add: mfrh_replace_rules filter allows you to personalize the renaming at the character level.

= 3.5.2 =
* Fix: Update system fixed and code cleaning.

= 3.4.5 =
* Fix: Better handling of umlauts.
* Info: There will be an important warning showing up during this update. It is an important announcement.

= 3.2.7 =
* Fix: Slug was not getting renamed after recent WP update.
* Fix: Tiny fixed to avoid notices.
* Add: Support for WPML Media (thanks to David García froml WPML Team).

= 3.2.4 =
* Fix: Should work with more plugins/themes, WooCommerce for example. The updates aren't done only on the full URLs of the images in the DB now but also on the relative uploads path as well.
* Info: If you have some time, please review me nicely at https://wordpress.org/support/view/plugin-reviews/media-file-renamer?rate=5#postform. Thanks to you, I get a lot of motivation to make this plugin better :)

= 3.2.3 =
* Add: Option to rename depending on the ALT. Useful if you get interesting information in your ALT.
* Update: Sync ALT also works with Attached Post Title.
* Fix: Better handling of norwegian letters (will improve this kind of things over time).

= 3.2.2 =
* Add: Rename the file during upload, based on the media title. Not by default, check the options :)

= 3.2.0 =
* Fix: Logging could not be enabled.
* Update: Code cleaning.

= 3.1.0 =
* Update: The UI was a bit modified and enhanced. I also think it is simpler and cleaner.
* Update: Removed the auto-flagging process which was causing issues on sizeable installs.

= 3.0.0 =
* Fix: The references in the excerpts are now also updated (they are used by WooCommerce).
* Add: Undo button. When the media is unlocked and has been renamed, you have a Undo button. You need to active this in the option.
* Update: Everything has been moved into the Meow Apps menu for a cleaner admin.

= 2.7.8 =
* Fix: Removed Flattr.
* Add: Additional cleaning to avoid extensions sometimes written in the title by WP.
* Add: Clean out the english apostrophe 's during the creation of the new filename.

= 2.7.6 =
* Add: New option to remove the ad, the Flattr button and the information message about the Pro.
* Fix: Renaming slug was not working well after latest WordPress updates
* Fix: Use direct links for all my images and links to follow WordPress rules.

= 2.7.1 =
* Info: A file mfrh_custom.php has been added. If you are an advanced users or a developer, please have a look at the FAQ here: https://wordpress.org/plugins/media-file-renamer/faq/. Since I am only one developer, I can't cover all the renaming cases we have (since sometimes plugins keep their own links to the filenames; such as WooCommerce). That will make it easy to advanced users to push Media File Renamer to cover more and more special cases.

= 2.6.9 =
* Change: Modified description and information about the mfrh_url_renamed and mfrh_media_renamed filters.
* Add: New option to force renaming file (even though the file failed to be renamed). That will help PRO users to fix their broken install, often after a migration for example (often related to change of hosting service using different encoding).
* Fix: Click on lock/unlock doesn't take you back to the first page anymore.
* Fix: Little naming issue when numbering + custom filter is used.

= 2.6.0 =
* Add: Lock/Unlock icons in the Media Library.
* Add: Rename depending on the title of the post the media is attached to.

= 2.5.0 =
* Update: WordPress 4.4.
* Add: Add -2, -3, etc... when filenames are similar. Pro only.
* Fix: There was a glitch when .jpeg extension were used. Now keep them as .jpeg.

= 2.4.0 =
* Fix: There was a possibility that the image sizes filenames could be overwritten wrongly.
* Update: Rename the GUID (File Name) is now the default. Too many people think it is a bug while it is not.
* Add: UTF-8 support for renaming files. Before playing with this, give it a try. Windows-based hosting service will probably not work well with this.
* Fix: Auto-Rename was renaming files even though it was disabled.
* Update: If Auto-Rename is disabled, the Media Library column is not shown anymore, neither is the dashboard (they are useless in that case).
* Add: Metadata containing '%20' instead of spaces are now considered too during the renaming.

= 2.3.0 =
* Add: Update the metadata (true by default).
* Fix: Guid was renamed wrongly in one rare case.
* Fix: Double extension issue with manual renaming.

= 2.2.4 =
* Fix: Couldn't rename automatically the files without changing the titles, now the feature is back.
* Fix: Better 'explanations' before renaming.
* Fix: Should work with WPML Media now.
* Fix: Manage empty filenames by naming them 'empty'.

= 2.2.2 =
* Add: Option to automatically sync the alternative text with the title.
* Add: Filters and Actions to allow plugins (or custom code) to customize the renaming.
* Fix: Avoid to rename file if title is not changed (annoying if you previously manually updated it).
* Change: Plugin functions are only loaded if the user is using the admin.

= 2.2.0 =
* Add: Many new options.
* Add: Pro version.
* Add: Manual file rename (Pro).
* Update: Use actions for renaming (to facilitate support for more renaming features).

= 2.0.0 =
* Fix: Texts.
* Fix: Versioning.

= 1.9.4 =
* Add: New option to avoid to modify database (no updates, only renaming).
* Add: New option to force update the GUID (aka "File name"...). Not recommended _at all_.
* Fix: Options were without effect.
* Fix: GUID issue.

= 1.3.4 =
* Fix: issue with attachments without metadata.
* Fix: UTF-8 title name (i.e. Japanese or Chinese characters).

= 1.3.0 =
* Add: option to rename the files automatically when a post is published.

= 1.2.2 =
* Fix: the 'to be renamed' flag was not removed in a few cases.

= 1.2.0 =
* Fix: issue with strong-caching with WP header images.
* Fix: now ignore missing files.
* Change: renaming is now part of the Media Library with nice buttons.
* Change: the dashboard has been moved to Tools (users should use the Media Library mostly).
* Change: no bubble counter on the dashboard menu; to avoid plugin to consume any resources.

= 1.0.4 =
* Fix: '<?' to '<?php'.
* Add: French translation.
* Change: Donation button (can be removed, check the FAQ).

= 1.0.2 =
* Fix: Ignore 'Header Image' to avoid related issues.
* Change: Updated screenshots.
* Change: 'To be renamed' filter removed (useless feature).

= 1.0.0 =
* Change: Rename Dashboard enhanced.
* Change: Scanning function now displays the results nicely.
* Change: Handle the media with 'physical' issues.

= 0.9.4 =
* Fix: Works better on Windows (file case).
* Fix: doesn't add numbering when the file exists already - was way too dangerous.
* Change: warns you if the Media title exists.
* Fix: Removed a 'warning'.

= 0.9 =
* Fix: Media were not flagged "as to be renamed" when the title was changed during editing a post.
* Change: Internal optimization.
* Add: Settings page.
* Add: Option to rename the slug or not (default: yes).

= 0.8 =
* Fix: Works with WP 3.5.
* Change: Update the links in DB directly.
* Fix: number of flagged media not updated straight after the mass rename.
* Fix: the "file name" in the media info was empty.
* Fix: SQL optimization & memory usage huge improvement.

= 0.5 =
* Add: New view "To be renamed" in the Media Library.
* Add: a nice counter to show the number of files that need to be renamed.
* Fix: the previous update (0.4) was actually not containing all the changes.

= 0.4 =
* Support for WPML
* Support for Retina plugins such as WP Retina 2x
* Adds a '-' between the filename and counter in case of similar files
* Mark the media as to be renamed when its name is changed outside the Media Library (avoid all the issues we had before)
* The GUID is now updated using the URL of the images and not the post ID + title (http://wordpress.org/support/topic/plugin-media-file-renamer-incorrect-guid-fix-serious-bug?replies=2#post-2239192).
* Double-check before physically renaming the files.

= 0.3 =
* Corrections + improvements.
* Handles well the 'special cases' now.
* Tiny corrections.

= 0.1 =
* First release.
