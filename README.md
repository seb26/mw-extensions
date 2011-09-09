MediaWiki Extensions
====================

A collection of extensions written by **seb26** for [MediaWiki](http://www.mediawiki.org/wiki/MediaWiki), an open-source wiki software package.

List
----

**LangUtils**

* SwitchString &ndash; 1.16.5+ &ndash; enables {{#langswitch:}} to display the correct translation depending on the page
* SidebarList &ndash; 1.14.0+ &ndash; adds a list of all existing translations to the navigation links (replaces manual solutions like {{languages}})
* PageClass &ndash; 1.17.0+ &ndash; adds "pagelang-xx" class to the <body> tag to allow language-specific CSS

Installation
------------

**LangUtils**

The following code should be added to `LocalSettings.php`:

    require_once( "$IP/extensions/LangUtils/LangUtils.php" );
        $wgLangUtilsSwitchString = true;
        $wgLangUtilsSidebarList = true;
        $wgLangUtilsPageClass = true; # Requires 1.17.0, set 'false' if lower.

Optional variables:

* `$wgAllowedLanguages` &ndash; array of strings containing all supported language codes
* `$wgLangUtilsSidebarListNS` &ndash; whitelist of namespaces to display the sidebar listing on (give as array of namespace constants, e.g. `NS_MAIN`)

Development
-----------

**TODO**

* None.

Licensing
---------

All extensions are copyright (c) 2011 **seb26**. Source code is free to be modified or distributed under the terms of the Modified BSD License. MediaWiki is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation.