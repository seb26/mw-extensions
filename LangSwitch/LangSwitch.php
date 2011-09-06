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
       'name' => 'LangSwitch',
       'author' => 'seb26', 
       'url' => 'https://github.com/seb26/mw-extensions', 
       'description' => 'Displays localized text based on the page\'s language suffix'
       );

# Set global variable defaults.
       
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
    
$wgLangSwitchAddBodyClass = false;
    
# Start.
    
$wgExtensionFunctions[] = 'wfLangSwitch_Setup';
$wgAutoloadClasses['ExtLangSwitch'] = dirname( __FILE__ );
 
function wfLangSwitch_Setup() {
    global $wgLsInstance, $wgHooks, $wgLangSwitchAddBodyClass;
    $wgLsInstance = new ExtLangSwitchInit;
    
    $wgHooks['ParserFirstCallInit'][] = array( &$wgLsInstance, 'registerParser' );
    
    # Magic word / variable registration
    $wgHooks['MagicWordwgVariableIDs'][] = array( &$wgLsInstance, 'declareMagicVar');
    $wgHooks['LanguageGetMagic'][] = array( &$wgLsInstance, 'registerMagic');
    $wgHooks['ParserGetVariableValueSwitch'][] = array( &$wgLsInstance, 'currentpagelang');

    return true;
}

class ExtLangSwitchInit {

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
			$this->realObj = new ExtLangSwitch;
			$this->realObj->clearState( $args[0] );
		}
		return call_user_func_array( array( $this->realObj, $name ), $args );
	}

}
 
class ExtLangSwitch { 

    function clearState( $parser ) {
        return true;
    }

    # Actual functions
    
    function currentpagelang( &$parser, &$cache, &$magicWordId, &$ret ) {
        if ( $magicWordId == 'currentpagelang' ) {
            if ( $parser->gotPageLang ) {
                $ret = $parser->mPageLang;
            }
            else {
                $parser->mPageLang = $this->getPageLang( $parser->getTitle()->mTextform );
                $parser->gotPageLang = true;
                $ret = $parser->mPageLang;
            }
        }
        return true;
    }

    function getPageLang( $title ) {
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

        return true;
    }
    
    function parseLang( $parser, $frame, $args ) {

        if ( $parser->gotPageLang == false) {
            $parser->mPageLang = $this->getPageLang( $parser->getTitle()->mTextform );
            $parser->gotPageLang = true;
        }

        if ( count( $args ) == 0 ) {
            return '';
        }
        
        # First argument is the 'force' parameter. Should be left undefined in wikisyntax, most of the time.
        # Depending on whether or not it is, $lang will serve as the working variable (force or not).

        $forceLang = trim( $frame->expand( $args[0] ) );
        if ( $forceLang == '' || $forceLang == null ) {
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