<?php
/************************************************************************/
/* QChecker (former AChecker)											*/
/* AChecker - https://github.com/inclusive-design/AChecker				*/
/************************************************************************/
/* Inclusive Design Institute, Copyright (c) 2008 - 2015                */
/* RELEASE Group And PT Innovation, Copyright (c) 2015 - 2016			*/
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

// Called by js request; forces downloading by sending headers and file content
// @ see checker/js/checker.js

/**
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

require (AC_INCLUDE_PATH.'config.inc.php');
require (AC_INCLUDE_PATH.'constants.inc.php');

	$path = $_GET['path'];
	$pattern_csv = '/achecker_(.*?)\.csv/';
	$pattern_rdf = '/achecker_(.*?)\.rdf/';
	$pattern_pdf = '/achecker_(.*?)\.pdf/';
	$pattern_html = '/achecker_(.*?)\.html/';
	if (preg_match($pattern_csv, $path, $match)) {
		$filename = $match[0];
	} else if (preg_match($pattern_rdf, $path, $match)) {
		$filename = $match[0];
	} else if (preg_match($pattern_pdf, $path, $match)) {
		$filename = $match[0];
	} else if (preg_match($pattern_html, $path, $match)) {
		$filename = $match[0];
	}
	
	if(strstr($path, AC_EXPORT_RPT_DIR)) {
        header('Content-Type: application/force-download');
        header('Content-transfer-encoding: binary'); 
        header('Content-Disposition: attachment; filename='.$filename);
        header('x-Sendfile: ', TRUE);
        readfile(trim($path));
	} else {
	    echo "nothing to download";
	}
?>