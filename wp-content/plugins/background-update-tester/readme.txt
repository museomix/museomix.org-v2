=== Background Update Tester ===
Contributors: wordpressdotorg, dd32, nacin
Tags: updates, background updates, automatic updates, tester, debug, developer
Requires at least: 3.7
Tested up to: 4.1
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Most sites are able to apply updates in the background. Background Update Tester checks your site for compatibility and explains any problems.

== Description ==

Most sites are able to apply updates in the background. Background Update Tester checks your site for compatibility and explains any problems.

After activating this plugin, visit the Dashboard → Update Tester screen. (If you are using multisite, visit Updates → Update Tester in the network admin.)

This is an initial release.

== Frequently Asked Questions ==

= Why can't my site apply background updates? =

There are many reasons why your site is not able to apply updates, like issues with file permissions or the inability to communicate securely with WordPress.org.

Install this plugin to see the specifics for your site.

For more technical details see (these two)[http://make.wordpress.org/core/2013/10/25/the-definitive-guide-to-disabling-auto-updates-in-wordpress-3-7/] (blog posts)[http://make.wordpress.org/core/2013/09/24/automatic-core-updates/].

= Why isn't this in WordPress core? =

We think at least 80 percent of installs do support background updates. For those remaining sites, updates are disabled for fairly complicated reasons, like use of version control, lack of OpenSSL support, and issues with file permissions. The recourse to resolve these issues is very difficult for the vast majority of users. Unfortunately it just isn't something we can or should ask of all users. A non-technical user, if they have the access to address these issues, is more likely to break their site than fix it.

== Changelog ==

= 1.1 =
* Prevent "checksum" failures when using a localized version of WordPress.
* Add early internationalization support. (Translations to follow.)

= 1.0 =
* Initial release.
