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

if ( !defined( 'MEDIAWIKI' ) ) { die( 'This file is a MediaWiki extension, it is not a valid entry point' ); }

$wgExtensionCredits['parserhook'][] = array(
   'name' => 'LangSidebar',
   'author' => 'seb26', 
   'url' => 'https://github.com/seb26/mw-extensions', 
   'description' => 'Several utilities to assist multilanguage wikis' # lololol
   );
   
# Globals

$wgAllowedLanguages = array( 
    'ar', 
    'cs', 
    'da', 
    'de', 
    'es', 
    'fi', 
    'fr', 
    'hu', 
    'it', 
    'ja', 
    'ko', 
    'nl', 
    'no', 
    'pl', 
    'pt', 
    'pt-br', 
    'ro', 
    'ru', 
    'sv', 
    'tr', 
    'zh-hans', 
    'zh-hant'
    );
    
$wgLangUtilsSwitchString = false;
$wgLangUtilsSidebarList = false;
    $wgLangUtilsSidebarListNS = array( 
        NS_TEMPLATE,
        NS_HELP,
        NS_TALK,
        NS_CATEGORY,
        NS_MAIN
        );
$wgLangUtilsPageClass = false;

# Setup
   
$wgExtensionFunctions[] = 'wfLangUtilsSetup';

function wfLangUtilsSetup() {
    global 
        $wgLangUtilsSwitchString,
        $wgLangUtilsSidebarList,
        $wgLangUtilsPageClass,
        
        $wgHooks,
        $wgAllowedLanguages;
        
    $wgAutoloadClasses['ExtLangUtils'] = dirname( __FILE__ );
        
    if ( $wgLangUtilsSwitchString ) {
        $wgAutoloadClasses['ExtLangUtilsSwitchString'] = dirname( __FILE__ );
        $wgLangUtilsSwitchInst = new ExtLangUtilsSwitchStringInit;
        
        $wgHooks['ParserFirstCallInit'][] = array( &$wgLangUtilsSwitchInst, 'registerParser' );
        $wgHooks['MagicWordwgVariableIDs'][] = array( &$wgLangUtilsSwitchInst, 'declareMagicVar');
        $wgHooks['LanguageGetMagic'][] = array( &$wgLangUtilsSwitchInst, 'registerMagic');
        $wgHooks['ParserGetVariableValueSwitch'][] = array( &$wgLangUtilsSwitchInst, 'currentpagelang');
    }
    
    if ( $wgLangUtilsSidebarList || $wgLangUtilsPageClass ) {
    
        $wgLangUtilsSkinInst = new ExtLangUtilsSkin;
        
        if ( $wgLangUtilsSidebarList ) {
            $wgHooks['SkinBuildSidebar'][] = array( &$wgLangUtilsSkinInst, 'addSidebar' );
        }
        if ( $wgLangUtilsPageClass ) {
            # Requires 1.17.0 and higher.
            $wgHooks['OutputPageBodyAttributes'][] = array( &$wgLangUtilsSkinInst, 'addBodyClass' );
        }
    }
    
    return true;
        
}

# Classes

class ExtLangUtils {

    # For obtaining the page language from a title string, preferably given from Title->mTextform.
    public static function getLang( $title ) {
        global $wgAllowedLanguages;
        
        if ( $title == null || $title == '' ) {
            return 'FAIL';
        }
        else {
            if ( strpos($title, '/') !== false ) {
                # Only process if there is a '/' in the title, waste of effort otherwise.
                # Return last occurence of "/xxx", then return "/xxx" without its first character.
                $sub = substr( strrchr($title, '/'), 1); 
                if ( in_array( $sub, $wgAllowedLanguages ) ) {
                    return $sub;
                }
                else {
                    return 'en';
                }
            }
            else { 
                # If there isn't, skip ze nonsense and set lang to 'en'.
                return 'en';
            }
        }

        return 'null';
    }
    
    # For obtaining the page language from a title or parts of a title already exploded with array().
    public static function getLangFromArray( $titleparts ) {
        global $wgAllowedLanguages;
        
        if ( in_array( end( $titleparts ), $wgAllowedLanguages ) ) {
            return end( $titleparts );
        }
        else {
            return 'en';
        }
    }
    
}


class ExtLangUtilsSkin {

    public function addSidebar( $skin, &$bar ) {
        global $wgContLang, $wgAllowedLanguages, $wgLangUtilsSidebarListNS;

        $title = $skin->mTitle;
        
        # Check the page's namespace is in the whitelist.
        if ( in_array( $title->mNamespace, $wgLangUtilsSidebarListNS ) ) {
            
            # The title of the page is split into an array per '/'.
            # The URLs, page prefixes and titles have to work differently depending on
            # whether or not the page is English (i.e. no lang /suffix) or not.
            
            # Remember that this extension always assumes that the English page (or 'root') exists,
            # and that the link to English in the sidebar will always be displayed, even if the
            # page itself does not exist.
            
            $tparts = explode( '/', $title );

            # Set page language, based on the last element in the array (i.e. "Page/foo/bar/ru" ).
            $pageLang = ExtLangUtils::getLangFromArray( $tparts );
            
            # If there are 2 or more title parts, there is some work to be done.
            if ( count( $tparts ) >= 2 ) {
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
            $output = '<ul>';
            $en = Title::newFromText( $tlangPrefix );
            $enUrl = $en->getLinkUrl();
            $output .= "<li id=\"n-en\"><a href=\"$enUrl\">English</a></li>";

            foreach ( $wgAllowedLanguages as $lang ) {  
                $tlang = Title::newFromText( $tlangPrefix . '/' . $lang );
                $id = $tlang->getArticleID();
                if ( $id !== 0 ) {
                    # The page ID was non-zero, meaning it's a valid page.
                    $localname = $wgContLang->getLanguageName( $lang ); # Is there a better way to do this?
                    $url = $tlang->getLinkUrl();
                    $output .= "<li id=\"n-$lang\"><a href=\"$url\">$localname</a></li>";
                }
                # If it is non-existent, output nothing.
            }

            $output .= '</ul>';
            $bar['Languages'] = $output; # Add the completed HTML to the sidebar.
        }
        
        return true;
        
    }
    
    public function addBodyClass( $out, $skin, &$bodyAttrs ) {
    
        $pageLang = ExtLangUtils::getLang( $out->mPagetitle );
        $bodyAttrs['class'] .= ' pagelang-' . $pageLang;
    
        return true;
    }
}

    
class ExtLangUtilsSwitchStringInit {

    var $realObj;

    function registerParser( &$parser ) {
        $parser->setFunctionHook( 'langswitch', array( &$this, 'parseLang' ), SFH_OBJECT_ARGS );
        $parser->setFunctionHook( 'currentpagelang', array( &$this, 'currentpagelang' ), SFH_NO_HASH );
        $parser->gotPageLang = false;
        return true;
    }
        
    function registerMagic( &$magicWords, $langCode ) {
        $magicWords['langswitch'] = array( 0, 'langswitch' );
        $magicWords['currentpagelang'] = array( 0, 'currentpagelang' );
        return true;
    }

    function declareMagicVar( &$customVariableIds ) {
        $customVariableIds[] = 'currentpagelang';
        return true;
    }

    # From ParserFunctions.php
    
	/** Defer ParserClearState */
	function clearState( $parser ) {
		if ( !is_null( $this->realObj ) ) {
			$this->realObj->clearState( $parser );
		}
		return true;
	}

	/** Pass through function call */
	function __call( $name, $args ) {
		if ( is_null( $this->realObj ) ) {
			$this->realObj = new ExtLangUtilsSwitchString;
			$this->realObj->clearState( $args[0] );
		}
		return call_user_func_array( array( $this->realObj, $name ), $args );
	}

}
 
class ExtLangUtilsSwitchString { 

    function clearState( $parser ) {
        return true;
    }
    
    function currentpagelang( &$parser, &$cache, &$magicWordId, &$ret ) {
        if ( $magicWordId == 'currentpagelang' ) {
            if ( $parser->gotPageLang ) {
                $ret = $parser->mPageLang;
            }
            else {
                $parser->mPageLang = ExtLangUtils::getLang( $parser->getTitle()->mTextform );
                $parser->gotPageLang = true;
                $ret = $parser->mPageLang;
            }
        }
        return true;
    }
    
    function parseLang( $parser, $frame, $args ) {

        if ( $parser->gotPageLang == false) {
            $parser->mPageLang = ExtLangUtils::getLang( $parser->getTitle()->mTextform );
            $parser->gotPageLang = true;
        }

        if ( count( $args ) == 0 ) {
            return '';
        }
        
        # First argument is the 'force' parameter. Should be left undefined in wikisyntax, most of the time.
        # Depending on whether or not it is, $lang will serve as the working variable (force or not).

        $forceLang = trim( $frame->expand( $args[0] ) );
        if ( $forceLang == '' || $forceLang == null ) {
            # $forceLang itself will always be given a value, but if it is empty / null,
            # assume that no language is being forced and just use the page lang.
            $lang = $parser->mPageLang;
        }
        else {
            $lang = $forceLang;
        }
        array_shift( $args ); # Remove first argument, as we cannot iterate over it (was made a string earlier).
        
        $en = '';
        $enFound = false;
        foreach ( $args as $arg ) {
            $bits = $arg->splitArg();
            $name = trim( $frame->expand( $bits['name'] ) );
            
            if ( $name == 'en' ) {
                # Found en, storing the node for laters
                $en = $bits['value'];
                $enFound = true;
            }
            if ( $name == $lang ) {
                return trim( $frame->expand( $bits['value'] ) );
            }
        }
        
        # $lang wasn't found. Put $en or blank string.
        if ( $enFound ) {
            return trim( $frame->expand( $en ) );
        }
        else {
            return '';
        }
        
    }
}