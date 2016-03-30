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
 * Extended Web Service interface for Quality Evaluation on a given URI.
 * For now Accessibility and Usability Guidelines are available.
 *
 * Parameters (required *)
 * uri*: 		URL of the page to evaluate.
 * 				Eg: uri=http://www.ubi.pt
 * id*: 		Web Service ID (available in under user profile).
 * guide: 		Guidelines to validate against,
 *        		use comma for multiple guidelines.
 * 				Eg: guide=WCAG2-AA,WCAG1-AA
 * check: 		Checkpoints to validate against,
 *        		use comma for multiple checkpoints.
 * 				Eg: check=ACR-1,ACR-101
 * context: 	Host.Driver to use in the evaluation.
 *				Eg: context=QChecker.PhantomJS
 * resolution:	Resolution (WidthxHeight) used in the evaluation.
 * 				Useful for responsive pages.
 * 				Eg: res=400x700
 * config:		Javascript config to use (same name as the js file)
 * 				Eg: default
 * port:		Port of the WebCrawler.
 * 				Eg: port=8080
 * offset:		The line offset to begin validation on the html output from URI.
 * 				Eg: 10
 * @author  Cindy Qi Li
 * @author  Joel Carvalho
 * @version 1.6.3 19/09/2015
 */

use QChecker\DAO\GuidelinesDAO;
use QChecker\DAO\GuidelineGroupsDAO;
use QChecker\DAO\GuidelineSubgroupsDAO;
use QChecker\DAO\SubgroupChecksDAO;
use QChecker\DAO\UserLinksDAO;
use QChecker\DAO\UsersDAO;
use QChecker\DAO\ChecksDAO;
use QChecker\Utils\HTMLWebServiceOutput;
use QChecker\Utils\Utility;
use QChecker\Validator\AccessibilityValidator;
use QChecker\Validator\HTMLRpt;
use QChecker\Validator\RESTWebServiceOutput;
use QChecker\DAO\ContextDAO;

/*
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

include_once(AC_INCLUDE_PATH. 'vitals.inc.php');
include_once(AC_INCLUDE_PATH. 'classes/Validator/HTMLRpt.class.php');
include_once(AC_INCLUDE_PATH. 'classes/Utility.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/UsersDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/GuidelineGroupsDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/GuidelineSubgroupsDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/SubgroupChecksDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/ChecksDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/UserLinksDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/Validator/AccessibilityValidator.class.php');
include_once(AC_INCLUDE_PATH. 'classes/Validator/HTMLWebServiceOutput.class.php');
include_once(AC_INCLUDE_PATH. 'classes/Validator/RESTWebServiceOutput.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/ContextDAO.class.php');

global $testExecutionTime;
$testExecutionTime=microtime(true);
$uri = trim(urldecode($_REQUEST['uri']));
Utility::stop404($uri);
$web_service_id = trim($_REQUEST['id']);
$guide = trim(strtolower($_REQUEST['guide']));
$check = trim(strtolower($_REQUEST['check']));
$output = trim(strtolower($_REQUEST['output']));
if (isset($_REQUEST['offset']))
	$offset = intval($_REQUEST['offset']);
else $offset=0;
$context_id = trim($_REQUEST['context']);
$contextDAO=new ContextDAO();
if (!is_numeric($context_id))
	$context_id=$contextDAO->getContextIdByCombinedName($context_id);

// initialize defaults for the ones not set or not set right but with default values
if ($output <> 'html' && $output <> 'rest') 
	$output = DEFAULT_WEB_SERVICE_OUTPUT;
// end of initialization

// validate parameters
if ($uri == '')
	$errors[] = 'AC_ERROR_EMPTY_URI';
else if (Utility::getValidURI($uri) === false)
    $errors[] = 'AC_ERROR_INVALID_URI';

if ($web_service_id == '')
	$errors[] = 'AC_ERROR_EMPTY_WEB_SERVICE_ID';
else { // validate web service id
	$usersDAO = new UsersDAO();
	$user_row = $usersDAO->getUserByWebServiceID($web_service_id);

	if (!$user_row)
        $errors[] = 'AC_ERROR_INVALID_WEB_SERVICE_ID';
	
	$user_id = $user_row['user_id'];
}

// return errors
if (is_array($errors)) {
	if ($output == 'rest') {
		header('Content-type: text/xml');
		echo RESTWebServiceOutput::generateErrorRpt($errors);
	} else
		echo HTMLRpt::generateErrorRpt($errors);
	exit;
}

// generate and filter specified checkpoints
$checks = null;
$checksDAO = new ChecksDAO();
if ($_REQUEST['check']!=''){
	$tmp=array_unique(explode(',',$check),SORT_REGULAR);
	$tmp=array_map(function($c){
		$checksDAO = new ChecksDAO();
		if (is_numeric($c)) return $c;
		else return $checksDAO->getCheckIdByAbbr($c);
	},$tmp);
	$tmp=$checksDAO->getAllOpenChecksByCheckIds($tmp);
	if (is_array($tmp))
		foreach($tmp as $c) $checks[]=$c['check_id'];
}

// generate guidelines, groups and subgroups
$guides = explode(',',$guide);
$guidelinesDAO = new GuidelinesDAO();
$guidelineGroupsDAO = new GuidelineGroupsDAO();
$guidelineSubgroupsDAO = new GuidelineSubgroupsDAO();
$subgroupChecks = new SubgroupChecksDAO();

foreach ($guides as $abbr) {
	if ($abbr == '') continue;
	$abbrArray=explode('.',$abbr);
	if (count($abbrArray)>1) {
		list($guideline_abbr, $group_abbr, $subgroup_abbr) = $abbrArray;

		$row=$guidelinesDAO->getGuidelineByAbbr($guideline_abbr);
		if ($row[0]['guideline_id'] <> '')
			$guideline_id=$row[0]['guideline_id'];
		else continue;

		$row=$guidelineGroupsDAO->getGroupByHierarchyAbbr($guideline_id, $group_abbr);
		if ($row[0]['group_id'] <> '')
			$group_id=$row[0]['group_id'];
		else continue;

		if (isset($subgroup_abbr))
			$subgroupRows = $guidelineSubgroupsDAO->getSubgroupByHierarchyAbbr($group_id, $subgroup_abbr);
		else
			$subgroupRows = $guidelineSubgroupsDAO->getSubgroupsByGroupID($group_id);
		if (!is_array($subgroupRows)) continue;

		foreach ($subgroupRows as $subgroup){
			$tmp=$subgroupChecks->getChecksIDBySubgroupID($subgroup['subgroup_id']);
			foreach($tmp as $c) $checks[]=$c['check_id'];
		}
		$options[]=array("guideline_id"=>$guideline_id,"abbr"=>$abbr);
	}
	else{
		$row = $guidelinesDAO->getEnabledGuidelinesByAbbr($abbr);
		if ($row[0]['guideline_id'] <> '')
			$gids[] = $row[0]['guideline_id'];
	}
}

// set to default guideline if no input guidelines and no checkpoints
if (!is_array($gids) && !is_array($checks) && empty($gids) && empty($checks))
    $gids[] = DEFAULT_GUIDELINE;

// retrieve user link ID
$userLinksDAO = new UserLinksDAO();
$user_link_id = $userLinksDAO->getUserLinkID($user_id, $uri, $gids);

// set new session id
$_POST["jsessionid"]=Utility::getSessionID();
$userLinksDAO->setLastSessionID($user_link_id, $_POST["jsessionid"]);

// Start Validation
$aValidator = new AccessibilityValidator($gids, $checks, $uri, $context_id);
$context_combined_name = $aValidator->getContextCombinedName();
$aValidator->setLineOffset($offset);
$aValidator->validate();
$errors = $aValidator->getValidationErrorRpt();

// save errors into user_decisions
//	$userDecisionsDAO = new UserDecisionsDAO();
//	$userDecisionsDAO->saveErrors($user_link_id, $errors);
if ($output == 'html') { // generate html output
	$htmlWebServiceOutput = new HTMLWebServiceOutput($aValidator, $user_link_id, $gids, $options);
	echo $htmlWebServiceOutput->getWebServiceOutput();
}

if ($output == 'rest') { // generate html output
	$restWebServiceOutput = new RESTWebServiceOutput($errors, $user_link_id, $gids);
	header('Content-type: text/xml');
	echo $restWebServiceOutput->getWebServiceOutput();
}

?>
