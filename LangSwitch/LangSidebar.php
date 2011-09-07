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
    $tparts = explode( '/', $title );
    
    if ( in_array( $title->mNamespace, $wgLangSidebarShowNS ) ) {
        # The page's namespace matches the whitelist.
        
        $pageCount = 0;
        $output = '<div class="portal"><ul>';
        
        foreach ( $wgAllowedLanguages as $lang ) {
            $tlang = Title::newFromText( $tparts[0] . '/' . $lang );
            $id = $tlang->getArticleID();
            if ( $id !== 0 ) {
                # If the page is a valid title.
                $pageCount += 1; # Increment this each time there is a good page. If it is 0 at the end, discard the div altogether.
                $localname = $wgContLang->getLanguageName( $lang );
                $url = $tlang->getLinkUrl();
                $output .= "<li id=\"n-$lang\"><a href=\"$url\">$localname</a></li>";
            }
        }
        
        if ( $pageCount > 0 ) {
            $output .= '</ul></div>';
            $bar['languages'] = $output; # Add the completed HTML to the sidebar.
        }
    }
    else {
        print_r( 'NS not in allowed list' );
    }
    
    return true;
    
}