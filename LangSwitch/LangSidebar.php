<?php

/* 
 * Copyright (c) 2011 seb26. All rights reserved.
 * Source code is licensed under the terms of the Modified BSD License.
 * 
 * MediaWiki is free software; you can redistribute it and/or modify it under the 
 * terms of the GNU General Public License as published by the Free Software Foundation.
 * <http://www.mediawiki.org/>
 * 
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['parserhook'][] = array(
       'name' => 'LangSidebar',
       'author' => 'seb26', 
       'url' => 'https://github.com/seb26/mw-extensions', 
       'description' => 'Displays links to translated pages in the sidebar'
       );
       
$wgLangSidebarShowNS = array( 
    NS_TEMPLATE,
    NS_HELP,
    NS_TALK,
    NS_CATEGORY,
    NS_MAIN
    );
       
$wgHooks['SkinBuildSidebar'][] = 'wfLangSidebar';

/*
 * This function works by creating a Title obj for each allowed language and checking if it has a valid ID.
 */
function wfLangSidebar( $skin, &$bar ) {
    global $wgContLang, $wgAllowedLanguages, $wgLangSidebarShowNS;

    $title = $skin->mTitle;
    
    # Check the page's namespace is in the whitelist.
    if ( in_array( $title->mNamespace, $wgLangSidebarShowNS ) ) {
        
        # The title of the page is split into an array per '/'.
        # The URLs, page prefixes and titles have to work differently depending on
        # whether or not the page is English (i.e. no lang /suffix) or not.
        
        # Remember that this extension always assumes that the English page (or 'root') exists,
        # and that the link to English in the sidebar will always be displayed, even if the
        # page itself does not exist.
        
        $tparts = explode( '/', $title );

        # Set page language, based on the last element in the array (i.e. "Page/foo/bar/ru" ).
        if ( in_array( end( $tparts ), $wgAllowedLanguages ) ) {
            $pageLang = end( $tparts );
        }
        else {
            $pageLang = 'en';
        }
        
        # If there are more than 2 title parts, there is some work to be done.
        if ( count( $tparts ) > 2 ) {
            if ( $pageLang == 'en' ) {
                $tlangPrefix = $title->mPrefixedText;
            }
            else {
                $tlangPrefix = implode( '/', array_slice( $tparts, 0, -1, true ) );
            }
        }
        else {
            $tlangPrefix = $tparts[0];
        }
        
        # Start building the list of links.
        $output = '<div class="portal"><ul>';
        $en = Title::newFromText( $tlangPrefix );
        $enUrl = $en->getLinkUrl();
        $output .= "<li id=\"n-en\"><a href=\"$enUrl\">English</a></li>";

        foreach ( $wgAllowedLanguages as $lang ) {  
            $tlang = Title::newFromText( $tlangPrefix . '/' . $lang );
            $id = $tlang->getArticleID();
            if ( $id !== 0 ) {
                # If the page is a valid title.
                $localname = $wgContLang->getLanguageName( $lang ); # Is there a better way to do this?
                $url = $tlang->getLinkUrl();
                $output .= "<li id=\"n-$lang\"><a href=\"$url\">$localname</a></li>";
            }
        }

        $output .= '</ul></div>';
        $bar['languages'] = $output; # Add the completed HTML to the sidebar.
    }
    
    return true;
    
}