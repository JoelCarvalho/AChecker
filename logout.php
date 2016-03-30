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

/*
* @ignore
*/
define('AC_INCLUDE_PATH', 'include/');

require(AC_INCLUDE_PATH.'vitals.inc.php');

// unset all session variables
session_unset();
$_SESSION = array();

$msg->addFeedback('LOGOUT');
header('Location: index.php');
exit;
?>