<?php

/*
 * Copyright (c) 2011 seb26. All rights reserved.
 * Source code is licensed under the terms of the 3-clause Modified BSD License.
 *
 * MediaWiki is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software Foundation.
 * <http://www.mediawiki.org/>
 *
 */

if ( !defined( 'MEDIAWIKI' ) ) { die( 'This file is a MediaWiki extension, it is not a valid entry point' ); }

$wgExtensionCredits['parserhook'][] = array(
	'name' => 'RevQuery',
	'author' => 'seb26',
	'url' => 'https://github.com/seb26/mw-extensions',
	'description' => 'Adds file timestamps to image URL links to work better with caching systems'
	);


$wgHooks['ImageBeforeProduceHTML'][] = 'wfRevQuery_thumbURL';
$wgHooks['ImagePageFileHistoryLine'] [] = 'wfRevQuery_fileHistory';

function wfRevQuery_thumbURL( &$skin, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res ) {

	$frameParams['url-query'] = 't=' . $file->timestamp;

	return true;
}
