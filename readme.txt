=== I am reading (continued) ===
Contributors: ginchen
Tags: Amazon,book,reading,widget
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 1.0.1

This is a re-upload of the (again) abandoned plugin "I am reading". Book display with search by ISBN, title and author for all Amazon market places with lots of individual display settings.

== Description ==

Easily configurable book display with sidebar widget and shortcode. Book information is read from Amazon Web Services (AWS) and gets cached in database.

*Key features:*

* Search for your current read book by title and / or author ***(new)***
* Search for your current read book by ISBN
* Easy setup of fonts and colors with live preview ***(new)***
* Ready for [Codestyling Localization](http://www.code-styling.de/english/development/wordpress-plugin-codestyling-localization-en) plugin

*Requirements*

* PHP version >= 5.1.2
* **free** [AWS Account](https://aws-portal.amazon.com/gp/aws/developer/registration/index.html)

== Installation ==

1. Unzip into your `/wp-content/plugins/` directory. If you're uploading it manually make sure to upload
the top-level folder. Don't just upload all the php files and put them in `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Type in your Amazon [Access Key ID](https://aws-portal.amazon.com/gp/aws/developer/account/index.html?ie=UTF8&action=access-key) and Secret Access Key or [register for free](https://aws-portal.amazon.com/gp/aws/developer/registration/index.html) first.

== Frequently Asked Questions ==

**What are the Amazon Web Services and do I have to pay for it ?**

You need to be a registered member of the Amazon Web Services to send search requests to Amazon. This is a free service providing product information about books and more, which is 100% free. You won't have to pay for.

**Can I use the plugin without a sidebar widget ?**

Yes, you can. Just use the shortcode [i-am-reading] or the PHP code `<?php iar_print_html(); ?>` to display your current read book in posts, pages or wherever you like.

== Upgrade Notice ==

Deactivate the plugin, update the files and activate it again. Don't just overwrite the plugin's files.

== Screenshots ==

1. Setup of fonts and colors with live preview
2. Book search by title and / or author
3. Demo widget at Ginchen's Blog