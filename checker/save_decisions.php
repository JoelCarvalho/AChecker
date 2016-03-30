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
// $Id: index.php 495 2011-02-10 21:27:00Z cindy $

/**
 * This is the web page called by ajax request from guideline view report -> "make decision(s)" buttons
 * And also called by ajax request from html report -> "Make decisions" button
 * @see checker/js/checker.js
 * @author  Cindy Qi Li
 * @author  Joel Carvalho
 * @version 1.6.1 17/08/2015
 */

use QChecker\Utils\Decision;
use QChecker\Utils\Utility;

/**
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

include(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH. 'classes/Utility.class.php');
include_once(AC_INCLUDE_PATH. 'classes/Decision.class.php');

// Begin Debug todo: Debug
error_log('[QCHECKER] [DECISIONS] user_id: '.$_SESSION['user_id']);
error_log('[QCHECKER] [DECISIONS] uri: '.$_POST['uri']);
error_log('[QCHECKER] [DECISIONS] output: '.$_POST['output']);
error_log('[QCHECKER] [DECISIONS] jsession_id: '.$_POST['jsessionid']);
// End Debug

// main process to save decisions
$decision = new Decision($_SESSION['user_id'], $_POST['uri'], $_POST['output'], $_POST['jsessionid']);

if ($decision->hasError()) {
	$decision_error = $decision->getErrorRpt();  // displays in checker_input_form.tmpl.php
	Utility::returnError($decision_error);
}
else
{
	// make decisions
	$decision->makeDecisions($_POST['d']);
	Utility::returnSuccess(_AC('saved_successfully'));
}

exit;
?>
