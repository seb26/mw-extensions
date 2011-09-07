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
    
    if ( in_array( $title->mNamespace, $wgLangSidebarShowNS ) ) {
        # The page's namespace matches the whitelist.
        
        $tparts = explode( '/', $title );
        $output = '<div class="portal"><ul>';
        
        $en = Title::newFromText( $tparts[0] );
        $enUrl = $en->getLinkUrl();
        
        $output .= "<li id=\"n-en\"><a href=\"$enUrl\">English</a></li>";
        
        foreach ( $wgAllowedLanguages as $lang ) {
            $tlang = Title::newFromText( $tparts[0] . '/' . $lang );
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