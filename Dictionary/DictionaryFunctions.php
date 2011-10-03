<?php

/* 
 * Copyright (c) 2011 seb26. All rights reserved.
 * Source code is licensed under the terms of the Modified BSD License.
 * 
 * MediaWiki is free software; you can redistribute it and/or modify it under the 
 * terms of the GNU General Public License as published by the Free Software Foundation.
 * <http:#www.mediawiki.org/>
 * 
 */
 
if ( !defined( 'MEDIAWIKI' ) ) { die( 'This file is a MediaWiki extension, it is not a valid entry point' ); }

class DictionaryFunctions {

    public function displayKeyValue( $parser, $frame, $args ) {
    
        global $wgAllowedLanguages;
    
        # Examples:
        # {{#dictionary:videos|flamethrower}} - Single value (group, key [len 2])
        # {{#dictionary:items|rocket launcher|lang=ru}} - Language (group, key, lang=xx [len 3])
        # {{#dictionary:steaminfo|seb26|value=steamid}} - Anymap (group, key, value=xx [len 3])
        
        # Expand group and key values.
        $group = trim( $frame->expand( $args[0] ) );
        $key = trim( $frame->expand( $args[1]['value'] ) );
        
        if ( count( $args ) > 2 ) {
            # Language & anymap types must have more than 2 arguments
            $keyType = $args[2];
            
            # Expand the name, then do stuff based on it.
            $name = trim( $frame->expand( $keyType['name'] ) );
            switch ( $name ) {
                case 'lang':
                    $pValue = trim( $frame->expand( $keyType['value'] ) );
                    if ( in_array( $pValue, $wgAllowedLanguages ) ) {
                        $req = 'key_value_' . $pValue;
                    }
                    break;
                case 'value':
                    # $pValue = trim( $frame->expand( $keyType['value'] ) );
                    $req = 'key_value_custom';
                    break;
                default:
                    return '';
            }
        }
        else {
            $req = 'key_value';
        }
        $groupId = DictionaryDb::getGroupAttrib( $group, 'group_name', 'group_id' );
        $result = DictionaryDb::getKeyValue( $groupId, $key, $req );
        return trim( $frame->expand( $result ) );

    }