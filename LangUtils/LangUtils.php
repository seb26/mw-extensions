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
   'name' => 'LangUtils',
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
        
        # Magic words & variables
        $wgHooks['MagicWordwgVariableIDs'][] = array( &$wgLangUtilsSwitchInst, 'declareMagicVar');
        $wgHooks['LanguageGetMagic'][] = array( &$wgLangUtilsSwitchInst, 'registerMagic');
        $wgHooks['ParserGetVariableValueSwitch'][] = array( &$wgLangUtilsSwitchInst, 'varGiveValue');
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
            return 'error';
        }
        
        $titleparts = explode( '/', $title );
        if ( count( $titleparts ) == 1 ) {
            # There are no slashes (definite English page).
            return 'en';
        }
        else {
            $end = end( $titleparts );
            # If the last element is a valid language suffix
            if ( in_array( $end, $wgAllowedLanguages ) ) {
                return $end;
            }
            else {
                return 'en';
            }
        }
        
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
        $parser->setFunctionHook( 'langswitch', array( &$this, 'langswitch' ), SFH_OBJECT_ARGS );
        $parser->setFunctionHook( 'ifpagelang', array( &$this, 'ifpagelang' ), SFH_OBJECT_ARGS );
        
        # Variables
        $parser->setFunctionHook( 'pagelang', array( &$this, 'varGiveValue' ), SFH_NO_HASH );
        $parser->setFunctionHook( 'pagelangsuffix', array( &$this, 'mgPageLangSuffix' ), SFH_NO_HASH );
        $parser->mPageLangSet = false;
        return true;
    }
        
    function registerMagic( &$magicWords, $langCode ) {
        $magicWords['langswitch'] = array( 0, 'langswitch' );
        $magicWords['ifpagelang'] = array( 0, 'ifpagelang' );
        $magicWords['pagelang'] = array( 0, 'pagelang' );
        $magicWords['pagelangsuffix'] = array( 0, 'pagelangsuffix' );
        return true;
    }

    function declareMagicVar( &$customVariableIds ) {
        $customVariableIds[] = 'pagelang';
        $customVariableIds[] = 'pagelangsuffix';
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
    
    function setLang( &$parser ) {
        if ( $parser->mPageLangSet == false) {
            $parser->mPageLang = ExtLangUtils::getLang( $parser->getTitle()->mTextform );
            $parser->mPageLangSet = true;
        }
        return true;
    }
    
    function varGiveValue( &$parser, &$cache, &$magicWordId, &$ret ) {
        $this->setLang( $parser );
        switch ( $magicWordId ) {
            case 'pagelang':
                # Simply return the page language as a magic word variable.
                $ret = $parser->mPageLang;
                break;
            case 'pagelangsuffix':
                # Returns '' for en pages and '/xx' for language pages. Replacement 
                # of {{if lang}} templates. More advanced needs can use {{#ifpagelang:}}
                # instead, as it has '$1' replacement features.
                if ( $parser->mPageLang == 'en' ) {
                    $ret = '';
                }
                else {
                    $ret = '/' . $parser->mPageLang;
                }
                break;
        }
        return true;
    }
    
    # {{#ifpagelang:}} will return a value similarily to {{#ifeq:}} based on the page's language. All
    # instances of '$1' inside each string will be replaced with the page's current language, to avoid
    # the need for {{PAGELANG}} calls inside each string; now it is just '$1'.
    function ifpagelang( $parser, $frame, $args ) {
        $this->setLang( $parser );
        
        $value = trim( $frame->expand( $args[0] ) ); # the first field (i.e. the lang code to check against)
        
        if ( $value == '' || $value == null ) {
            $value = 'en';
        }
        
        $true = isset( $args[1] ) ? $args[1] : '';
        $false = isset( $args[2] ) ? $args[2] : '';

        if ( $parser->mPageLang == $value ) {
            return str_replace( '$1', $parser->mPageLang, trim( $frame->expand( $true ) ) ); # Expand the 'True' field.
        }
        else {
            return str_replace( '$1', $parser->mPageLang, trim( $frame->expand( $false ) ) ); # Expand the 'False' field.
        }

    }
    
    function langswitch( $parser, $frame, $args ) {

        $this->setLang( $parser );

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