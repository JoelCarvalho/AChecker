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

use QChecker\DAO\GuidelinesDAO;
use QChecker\DAO\ContextDAO;
use QChecker\DAO\UserLinksDAO;
use QChecker\Utils\Decision;
use QChecker\Utils\Utility;
use QChecker\Validator\AccessibilityValidator;
use QChecker\Validator\CSSValidator;
use QChecker\Validator\HTMLValidator;

/**
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

include(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH. 'classes/Utility.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/GuidelinesDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/ChecksDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/UserLinksDAO.class.php');
include_once(AC_INCLUDE_PATH. 'classes/Decision.class.php');
include_once(AC_INCLUDE_PATH. 'classes/DAO/ContextDAO.class.php');
include(AC_INCLUDE_PATH. "classes/Validator/AccessibilityValidator.class.php");

global $starttime;
$mtime = microtime(); 
$mtime = explode(" ", $mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime; 

// input_form - array in session that contains latest user request (needed for file export)
unset($_SESSION['input_form']); 

$guidelinesDAO = new GuidelinesDAO();
$context = new ContextDAO();

// process to make decision
if (isset($_POST['make_decision']) || isset($_POST['reverse'])) {
	$decision = new Decision($_SESSION['user_id'], $_POST['uri'], $_POST['output'], $_POST['jsessionid']);
	
	if ($decision->hasError()) {
		$decision_error = $decision->getErrorRpt();  // displays in checker_input_form.tmpl.php
	}
	else
	{
		// make decisions
		if (isset($_POST['make_decision'])) $decision->makeDecisions($_POST['d']);
		
		// reverse decision
		if (isset($_POST['reverse'])) {
			foreach ($_POST['reverse'] as $sequenceID => $garbage)
				$decision->makeDecisions(array($sequenceID=>AC_NO_DECISION));
		}
	}
}
// end of process to made decision

// validate referer URIs that has passed validation and received seal. The click on the seal triggers 
// the if - else below.
if (isset($_GET['uri']) && $_GET['uri'] == 'referer') {
	// validate if the URI from referer matches the URI defined in user_links.user_link_id
	if (isset($_GET['id'])) {
		$userLinksDAO = new UserLinksDAO();
		$row = $userLinksDAO->getByUserLinkID($_GET['id']);
		
		$pos_user_link_uri = strpos($row['URI'], '?');
		if ($pos_user_link_URI > 0) $user_link_uri = substr($row['URI'], 0, $pos_user_link_uri);
		else $user_link_uri = $row['URI'];

		$pos_referer_uri = strpos($_SERVER['HTTP_REFERER'], '?');
		if ($pos_referer_uri > 0) $referer_uri = substr($_SERVER['HTTP_REFERER'], 0, $pos_referer_uri);
		else $referer_uri = $_SERVER['HTTP_REFERER'];
		
		// guideline id must be given if the request is to check referer URI
		if (!isset($_GET['gid']))
			$msg->addError('EMPTY_GID');
		else
		{
			$grow = $guidelinesDAO->getGuidelineByAbbr($_GET['gid']);
			if (!is_array($grow))
				$msg->addError('INVALID_GID');
		}
		
		if (!stristr($referer_uri, $user_link_uri))
			$msg->addError('REFERER_URI_NOT_MATCH');
		
		if (isset($_SESSION['user_id']) && $_SESSION['user_id'] <> $row['user_id'])
			$msg->addError('USER_NOT_MATCH');
	}
	
	if (!$msg->containsErrors()) {
		$_POST['validate_uri'] = 1;
		$_POST['uri'] = $_SERVER['HTTP_REFERER'];
		$_gids = array($grow[0]['guideline_id']);
	}
}

// a flag to record if there's problem validating html thru 3rd party web service
$error_happen = false;

// CSS Validation
if (isset($_POST["enable_css_validation"])) {
	include(AC_INCLUDE_PATH. "classes/Validator/CSSValidator.class.php");
	$_SESSION['input_form']['enable_css_validation'] = true;
}

// validate html
if (isset($_POST["enable_html_validation"])) {
	include(AC_INCLUDE_PATH. "classes/Validator/HTMLValidator.class.php");
	$_SESSION['input_form']['enable_html_validation'] = true;
}

if (!isset($_gids) || !is_array($_gids)) { // $_gids hasn't been set at validating referer URIs
	if (isset($_POST["rpt_format"]) && $_POST["rpt_format"] == REPORT_FORMAT_GUIDELINE)
		$_gids = $_POST["radio_gid"];
	else if (isset($_POST["rpt_format"]) && $_POST["rpt_format"] == REPORT_FORMAT_LINE)
		$_gids = $_POST["checkbox_gid"];
	else if(isset($_POST["gid"]))
		$_gids = $_POST["gid"];
	else
		$_gids = null;
	$_SESSION['input_form']['gids'] = $_gids;
}

if (isset($_POST["validate_uri"]) && $_POST["validate_uri"]) {
	$_POST['uri'] = htmlentities($_POST['uri']);
	
	$uri = Utility::getValidURI(addslashes($_POST["uri"]));
	$_SESSION['input_form']['uri'] = $uri;
	
	// Check if the given URI is connectable
	if ($uri === false) {
		$msg->addError(array('CANNOT_CONNECT', $_POST['uri']));
	}
	
	// don't accept localhost URI
	if (stripos($uri, '://localhost') > 0) {
		$msg->addError('NOT_LOCALHOST');
	}
	
	if (!$msg->containsErrors()) {
		$_POST['uri'] = $_REQUEST['uri'] = $uri;

		if (isset($_POST["enable_html_validation"]))
			$htmlValidator = new HTMLValidator("uri", $uri);
	
		//CSS Validator
		if (isset($_POST["enable_css_validation"]))
			$cssValidator = new CSSValidator("uri", $uri);	

		if (isset($_POST["show_source"]))
			$source_array = file($uri);
	}
}

if (isset($_POST["validate_content"]) && $_POST["validate_content"] <> '') {
	$validate_content = $_POST["validate_content"];
	if (isset($_POST["show_source"]))
		$source_array = explode("\n", $_POST["validate_content"]);
}
// end of validating html

$has_enough_memory = true;
if (isset($validate_content) && !Utility::hasEnoughMemory(strlen($validate_content))) {
	$msg->addError('NO_ENOUGH_MEMORY');
	$has_enough_memory = false;
}

// A boolean flag that decides the show/hide of the AChecker introduction section.
// This section is displayed when the AChecker index page is visited and no validation has been performed yet. 
$show_achecker_whatis = false;

// validation and display result
if (isset($_POST["validate_uri"]) || isset($_POST["validate_file"]) || isset($_POST["validate_content"]) || isset($_POST["validate_paste"])) {
	// check accessibility
	//include(AC_INCLUDE_PATH. "classes/Validator/AccessibilityValidator.class.php");

	if ($_POST["validate_uri"]) $check_uri = $_POST['uri'];
	
	if ($has_enough_memory) {
		$aValidator = new AccessibilityValidator($_gids, null, $check_uri, $_POST['val_context'][0]);
		$aValidator->validate();
	}
	// end of checking accessibility
}
else
{
	$show_achecker_whatis = true;
}

$has_errors = false;  // A flag detecting if there's any error occurred

if ($msg->containsErrors()) {
	$has_errors = true;
}

// display initial validation form: input URI or upload a html file 
include ("checker_input_form.php");

// display validation results
if (!$has_errors && (isset($aValidator) || isset($htmlValidator))) {
	include ("checker_results.php");
}
else
{
	$show_achecker_whatis = true;
}

if ($show_achecker_whatis) {
	echo '<div id="output_div" class="validator-output-form">';
	echo "<p>"._AC('qchecker_whatis')."</p>";
	echo '</div>';
}

// display footer
include(AC_INCLUDE_PATH.'footer.inc.php');

?>
