=== Plugin Name ===
Contributors: zerotop
Donate link: http://www.benjamindejong.com/your-tables
Tags: mysql,forms,table,crud
Requires at least: 3.1.0
Tested up to: 4.5.2
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugins lets you define forms in the admin area for existing tables in your WordPress database.

== Description ==

This plugins lets you define forms in the admin area for existing tables in your WordPress database.

The creation of forms to Create, Update and Delete records is a lot of work. This plugin lets you define the tables and its fields and generates the form for you. The definition of your tables and fields is done in the 'Your Tables' and 'Your Table Fields' items in the 'Your Tables' menu. This functionality is created using the Table definition functionality of this plugin itself. Drink your own Champagne!

These are its features:

*   Manage all tables in your WordPress database in the admin area
*   Create, Update and optionaly Delete records
*   Textbox, Dropdown, List, Radiobox, Checkbox, Date and Textarea support
*   Dropdown, List and Radioboxes can be filled with predefined values or with an SQL query that results in 2 columns (value,label)
*   Use one or more primary keys to select the correct record in your tables
*   Define what roles can use the tables
*   Optionaly change the functionality of the Table and Field definition forms
*   Language support (Currently English and Dutch language is available. We're looking for translators!)

Request: Currently English and Dutch language is available. If you want to translate for us we will add your name here:

*   English translation: Benjamin de Jong
*   Dutch translation: Benjamin de Jong

== Installation ==

This section describes how to install the plugin and get it working.

From your WordPress dashboard:

*   Visit 'Plugins > Add New'
*   Search for 'Your Tables'
*   Click 'Install now'
*   Activate it after installation
*   Make the functionality available for other roles if needed using 'Your Tables Settings' under the WordPress Settings menu.

From WordPress.org:

*   Download 'Your Tables'.
*   Upload the 'your-tables' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
*   Activate Your Tables from your Plugins page
*   Make the functionality available for other roles if needed using 'Your Tables Settings' under the WordPress Settings menu.

== Screenshots ==

1. An example of the list of records you can edit in a table
2. An example of an edit form your a record in your table
3. The definition form for one field of your table

== Changelog ==

= 1.0.0 =
* Created the initial version

= 1.0.3 =
* Minor buxfix on non existing field names
* SQL errors are showed in an orderly fashion
* Support for localized dates (please make sure your webserver supports localization) including jQuery datepicker translation

= 1.0.4 =
* Bugfix on invalid header
* Fixed some unneeded includes
