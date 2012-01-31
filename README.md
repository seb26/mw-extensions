LangUtils
====================

**LangUtils** is an extension for [MediaWiki](http://www.mediawiki.org/wiki/MediaWiki), an open-source wiki software package, written by **seb26**. To wikis that support multiple languages on the same site, LangUtils provides extra tools and functionality for navigation and displaying language-specific content.

Functionality
----

* LangSwitch &ndash; 1.16.5+ &ndash; enables {{#langswitch:}} to display the correct translation depending on the page
* SidebarList &ndash; 1.14.0+ &ndash; adds a list of all existing translations to the navigation links (replaces manual solutions like {{languages}})
    * SidebarList also provides links for file pages (for "File:Test.png", it will link to "File:Test fr.png", "File:Test de.png", etc).
* PageClass &ndash; 1.17.0+ &ndash; adds "pagelang-xx" class to the `<body>` tag to allow language-specific CSS

Installation
------------

The following code should be added to `LocalSettings.php`:

    require_once( "$IP/extensions/LangUtils/LangUtils.php" );

Optional variables:

* `$wgLangUtils_LangSwitch` &ndash; set to false to disable {{#langswitch:}}
* `$wgLangUtils_SidebarList` &ndash; set to false to disable the sidebar list
    * `$wgLangUtils_SidebarList_NS` &ndash; whitelist of namespaces to display the sidebar listing on (give as array of namespace constants, e.g. `NS_MAIN`)
* `$wgLangUtils_PageClass` &ndash; set to false to disable language CSS class (**Note**: this must be disabled on installations lower than 1.17.0).
* `$wgAllowedLanguages` &ndash; array of strings containing all supported language codes

Development
-----------

**Testing**

* Developed for stable branch (currently **1.18.1**), tested on this version.
* Untested on 1.17.1 or lower.

**TODO**

* None.

Licensing
---------

All extensions are copyright (c) 2011 **seb26**. Source code is free to be modified or distributed under the terms of the Modified BSD License. MediaWiki is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation.