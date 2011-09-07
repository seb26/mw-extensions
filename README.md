MediaWiki Extensions
====================

A collection of extensions written by **seb26** for [MediaWiki](http://www.mediawiki.org/wiki/MediaWiki), an open-source wiki software package.

List
----

* **LangSwitch** &ndash; displays translated strings according to the page language.
* **LangSidebar** &ndash; adds links to sidebar for navigation between different language pages.

Installation
------------

**LangSwitch** &ndash; tested on MediaWiki 1.16.5+
    
    $wgAllowedLanguages = array( 'ar', 'cs', 'da', ... );
    require_once( "$IP/extensions/LangSwitch/LangSwitch.php" );
    require_once( "$IP/extensions/LangSwitch/LangSidebar.php" );
    
Hooks:

* LangSwitch: ParserFirstCallInit, MagicWordwgVariableIDs, LanguageGetMagic, ParserGetVariableValueSwitch
* LangSidebar: SkinBuildSidebar

Development
-----------

**TODO**

* None.

Licensing
---------

All extensions are copyright (c) 2011 **seb26**. Source code is free to be modified or distributed under the terms of the Modified BSD License. MediaWiki is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation.