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

/**
 * This is the web service interface to check accessibility on a given URI
 * Expected parameters:
 * input: Host name
 * output: All contexts for the specified Host
 * @author  Joel Carvalho
 * @version 1.6.2 07/09/2015
 */

use QChecker\DAO\ContextDAO;

/*
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

include(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/ContextDAO.class.php');

$host_name=null;
$host_id=null;
$all=false;

if (isset($_REQUEST['host_name']))
    $host_name = trim(urldecode($_REQUEST['host_name']));
if (isset($_REQUEST['host_id']))
    $host_id = trim(urldecode($_REQUEST['host_id']));
if (isset($_REQUEST['all']) && $_REQUEST['all']==='true')
    $all = true;

if (is_numeric($host_id))
    $host_id=intval($host_id);

if ($host_id<0) $host_id=0;

$context = new ContextDAO();

if ($host_id!=null)
    $res=$context->getAllContextByHost($host_id);
else if ($host_name!=null)
    $res=$context->getAllContextByHost($host_name);
else if($all===true)
    $res=$context->getAll();
else
    $res=false;

echo json_encode($res);

?>
