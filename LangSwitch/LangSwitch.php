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

       
$wgExtensionFunctions[] = 'wfLangSwitch_Setup';

$wgLangSwitchAllowedLangs = array( 
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
 
function wfLangSwitch_Setup() {
    global $wgLsInstance, $wgHooks;
    $wgLsInstance = new wfLangSwitch;
    
    $wgHooks['ParserFirstCallInit'][] = array( &$wgLsInstance, 'registerParser' );
    $wgHooks['ParserFirstCallInit'][] = array( &$wgLsInstance, 'getPageLang' );
    
    $wgHooks['ParserGetVariableValueSwitch'][] = array( &$wgLsInstance, 'getLangVar');
    $wgHooks['MagicWordwgVariableIDs'][] = array( &$wgLsInstance, 'declareMagicVar');
    $wgHooks['LanguageGetMagic'][] = array( &$wgLsInstance, 'registerMagic');
    
    return true;
}
 
class wfLangSwitch { 

    # Variables
    
    var $pageLang = '';

    # Setup functions
    
    function registerParser( &$parser ) {
        $parser->setFunctionHook( 'langswitch', array( &$this, 'parseLang' ), SFH_OBJECT_ARGS );
        # $parser->setFunctionHook( 'setpagelang', array( &$this, 'setLang' ), SFH_NO_HASH );
        # $parser->setFunctionHook( 'getLangVar', array( &$this, 'getLangVar' ), SFH_NO_HASH );
        return true;
    }
        
    function registerMagic( &$magicWords, $langCode ) {
        $magicWords['langswitch'] = array( 0, 'langswitch' ); # The parser function itself.
        $magicWords['currentpagelang'] = array( 0, 'currentpagelang' ); # The reusable {{CURRENTPAGELANG}} which simply recites the value.
        return true;
    }
    
    function setPageLang( &$parser ) {
        $this->getPageLang( $parser );
        return true;
    }

    function declareMagicVar( &$customVariableIds ) {
        $customVariableIds[] = 'currentpagelang';
        return true;
    }

    # Actual functions
    
    function getPageLang( $parser ) {
        global $wgLangSwitchAllowedLang;
        $title = $parser->getTitle();
        # TODO: Consider ditching these string methods and see if $parser can spit out a Title object instead.
        if ( strpos($title, '/') !== false ) {
            # If there is a '/' in the title...
            $sub = substr( strrchr($title, '/'), 1); # Return last occurence of "/xxx", then return "/xxx" without its first character.
            if ( in_array( $sub, array('en', 'ru', 'fr') ) ) {
                $this->pageLang = $sub;
                # return $sub;
            }
            else {
                $this->pageLang = 'en';
                # return 'en';
            }
        }
        else { 
            # If there isn't, skip ze nonsense and set lang to 'en'.
            $this->pageLang = 'en';
            # return 'en';
        }
        return true;
    }
    
    function getLangVar( &$parser, &$cache, &$magicWordId, &$ret ) {
        if ( $magicWordId == 'currentpagename' ) {
            $ret = $this->pageLang;
        }
        return true;
    }
    
    function parseLang( $parser, $frame, $args ) {
        if ( count( $args ) == 0 ) {
            return '';
        }
        
        # First argument is the 'force' parameter. Should be left undefined in wikisyntax, most of the time.
        # Depending on whether or not it is, $lang will serve as the working variable (force or not).

        $forceLang = trim( $frame->expand( $args[0] ) );
        if ( $forceLang == '' ) {
            $lang = $this->pageLang;
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
                # Found en, storing it for laters
                $en = trim( $frame->expand( $bits['value'] ) );
                $enFound = true;
            }
            if ( $name == $lang ) {
                return trim( $frame->expand( $bits['value'] ) );
            }
        }
        
        # $lang wasn't found. Put $en or blank string.
        if ( $enFound ) {
            return $en;
        }
        else {
            return '';
        }
        
    }
}