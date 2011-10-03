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

class DictionaryDb { 

    // Read functions

    public static function getKeyAttrib( $key, $have, $need ) {
        
        $dbRead = wfGetDB( DB_SLAVE );
        return $dbRead->select(
            'dictionary_keys',
            $need,
            array( $have => $key ),
            __METHOD__
        );
    }

    public static function getKeyValue( $groupId, $key, $values ) {
    
        $dbRead = wfGetDB( DB_SLAVE );
        $result = $dbRead->select(
            'dictionary_keys',
            $values,
            array( 'key_group_id' => $groupId ),
            __METHOD__
        );
        return $result;
    
    }
    
    public static function getGroupId( $group ) {
        $dbRead = wfGetDB( DB_SLAVE );
        return $dbRead->select(
            'dictionary_groups',
            'group_id',
            array( 'group_name' => $group ),
            __METHOD__
        );
    }
    
    // Write functions
    
    public static function createKey( $groupId, $key, $type, $text, $textLang ) {
    
        // Set defaults
        $values = array( 
            'key_group_id' => $groupId,
            'key_name' => $key,
            'key_type' => 0
        );
        
        switch ( $type ) {
            case 'value':
                $values['key_type'] = 0;
                break;
            case 'lang':
                $values['key_type'] = 1;
                $values = array_merge( $values, $textLang );
                break;
            case 'anymap':
                $values['key_type'] = 2;
                break;
        }
        
        $dbWrite = wfGetDB( DB_MASTER );
        $dbWrite->insert(
            'dictionary_keys',
            $values,
            __METHOD__
        );
        
        return true;
        
    }
    
    public static function setKeyValue( $groupId, $keyId, $value, $text ) {
        
        $dbWrite = wfGetDB( DB_MASTER );
        $dbWrite->update(
            'dictionary_keys',
            array( $value, $text ),
            array( 
                'key_id' => $keyId,
                'group_id' => $groupId
            ),
            __METHOD__
        );
        
        return true;
        
    }
    
        // Group functions
        
    public static function createGroup( $groupName ) {
        
        $dbWrite = wfGetDB( DB_MASTER );
        $dbWrite->insert(
            'dictionary_groups',
            array( 'group_name' => $groupName ),
            __METHOD__
        );
        
        return true;
        
    }
    
    public static function updateGroupAttribs( $groupId, $attrib, $newvalue ) {
    
        $dbWrite = wfGetDB( DB_MASTER );
        $dbWrite->update(
            'dictionary_groups', // Update this table
            array( $attrib => $newvalue ), // The row 'attrib' with the new value
            array( 'group_id' => $groupId ), // where the group_id matches the given argument.
            __METHOD__
        );
        
    }
    
}